<?php
declare (strict_types = 1);

namespace Cyndaron\Ticketsale;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    protected array $postRoutes = [
        'add' => ['level' => UserLevel::ANONYMOUS, 'function' => 'add'],
    ];
    protected array $apiPostRoutes = [
        'delete' => ['level' => UserLevel::ADMIN, 'function' => 'delete'],
        'setIsPaid' => ['level' => UserLevel::ADMIN, 'function' => 'setIsPaid'],
        'setIsSent' => ['level' => UserLevel::ADMIN, 'function' => 'setIsSent'],
    ];

    protected function add(): Response
    {
        $concertId = (int)Request::post('concert_id');
        try
        {
            $this->processOrder($concertId);

            $page = new Page(
                'Bestelling verwerkt',
                'Hartelijk dank voor uw bestelling. U ontvangt binnen enkele minuten een e-mail met een bevestiging van uw bestelling en betaalinformatie.'
            );
            return new Response($page->render());
        }
        catch (Exception $e)
        {
            $page = new Page('Fout bij verwerken bestelling', $e->getMessage());
            return new Response($page->render());
        }
    }

    /**
     * @param $concertId
     * @throws Exception
     */
    private function processOrder($concertId): void
    {
        if (Request::postIsEmpty())
        {
            throw new InvalidOrder('De bestellingsgegevens zijn niet goed aangekomen.');
        }

        /** @var Concert $concertObj */
        $concertObj = Concert::loadFromDatabase($concertId);

        if (!$concertObj->openForSales)
        {
            throw new InvalidOrder('De verkoop voor dit concert is helaas gesloten, u kunt geen kaarten meer bestellen.');
        }

        $postcode = Request::post('postcode');
        $addressIsAbroad = Request::post('country') === 'abroad';
        $deliveryByMember = (bool)Request::post('deliveryByMember');
        $deliveryByMember = $addressIsAbroad ? true : $deliveryByMember;
        $deliveryMemberName = Request::post('deliveryMemberName');

        $incorrecteVelden = $this->checkForm($concertObj->forcedDelivery, $deliveryByMember);
        if (!empty($incorrecteVelden))
        {
            $message = 'De volgende velden zijn niet goed ingevuld of niet goed aangekomen: ';
            $message .= implode(', ', $incorrecteVelden) . '.';
            throw new InvalidOrder($message);
        }

        $totaalprijs = 0.0;
        $totaalAantalKaarten = 0;

        if ($concertObj->forcedDelivery)
        {
            $qualifiesForFreeDelivery = ($addressIsAbroad) ? false : Util::postcodeQualifiesForFreeDelivery((int)$postcode);

            if ($qualifiesForFreeDelivery)
            {
                $payForDelivery = false;
                $deliveryByMember = false;
            }
            else if ($deliveryByMember)
            {
                $payForDelivery = false;
            }
            else
            {
                $payForDelivery = true;
            }
        }
        else
        {
            $payForDelivery = Request::post('bezorgen') ? true : false;
        }
        $deliveryPrice = $payForDelivery ? $concertObj->deliveryCost : 0.0;
        $reserveSeats = Request::post('hasReservedSeats') ? 1 : 0;
        $toeslag_gereserveerde_plaats = ($reserveSeats === 1) ? $concertObj->reservedSeatCharge : 0;
        $order_tickettypes = [];
        $ticketTypes = DBConnection::doQueryAndFetchAll('SELECT * FROM ticketsale_tickettypes WHERE concertId=? ORDER BY price DESC', [$concertId]);
        foreach ($ticketTypes as $ticketType)
        {
            $order_tickettypes[$ticketType['id']] = (int)Request::post('tickettype-' . $ticketType['id']);
            $totaalprijs += $order_tickettypes[$ticketType['id']] * ($ticketType['price'] + $deliveryPrice + $toeslag_gereserveerde_plaats);
            $totaalAantalKaarten += $order_tickettypes[$ticketType['id']];
        }

        if ($totaalprijs <= 0)
        {
            throw new InvalidOrder('U heeft een bestelling van 0 kaarten geplaatst of het formulier is niet goed aangekomen.');
        }

        $email = Request::post('email');
        $lastName = Request::post('lastName');
        $initials = Request::post('initials');
        $street = Request::post('street');
        $postcode = Request::post('postcode');
        $city = Request::post('city');
        $comments = Request::post('comments');

        $result = DBConnection::doQuery('INSERT INTO ticketsale_orders
            (`concertId`, `lastName`, `initials`, `email`, `street`, `postcode`, `city`, `delivery`,               `hasReservedSeats`, `deliveryByMember`,      `deliveryMemberName`, `addressIsAbroad`,      `comments`) VALUES
            (?,           ?,          ?,          ?,       ?,        ?,          ?,      ?,                        ?,                  ?,                       ?,                    ?,                      ?)',
            [$concertId,  $lastName,  $initials,  $email,  $street,  $postcode,  $city, ($payForDelivery ? 1 : 0), $reserveSeats,      (int)$deliveryByMember,  $deliveryMemberName,  (int)$addressIsAbroad,  $comments]);
        if ($result === false)
        {
            throw new InvalidOrder('Opslaan bestelling mislukt!');
        }
        $orderId = (int)$result;

        foreach ($ticketTypes as $ticketType)
        {
            if ($order_tickettypes[$ticketType['id']] > 0)
            {
                $result = DBConnection::doQuery(
                    'INSERT INTO ticketsale_orders_tickettypes(`orderId`, `tickettypeId`, `amount`) VALUES(?, ?, ?)',
                    [$orderId, $ticketType['id'], $order_tickettypes[$ticketType['id']]]);
                if ($result === false)
                {
                    throw new InvalidOrder('Opslaan kaarttypen mislukt!');
                }
            }
        }

        $reservedSeats = null;
        if ($reserveSeats === 1)
        {
            $reservedSeats = $concertObj->reserveSeats($orderId, $totaalAantalKaarten);
            if ($reservedSeats === null)
            {
                DBConnection::doQuery('UPDATE ticketsale_orders SET hasReservedSeats = 0 WHERE id=?', [$orderId]);
                $totaalprijs -= $totaalAantalKaarten * $toeslag_gereserveerde_plaats;
                $reserveSeats = -1;
            }
        }

        $this->sendMail($payForDelivery, $concertObj, $deliveryByMember, $deliveryMemberName, $reserveSeats, $reservedSeats, $totaalprijs, $orderId, $ticketTypes, $order_tickettypes, $lastName, $initials, $street, $postcode, $city, $comments, $email);
    }

    private function checkForm($forcedDelivery = false, $memberDelivery = false): array
    {
        $incorrecteVelden = [];
        if (strtoupper(Request::post('antispam')) !== 'VLISSINGEN')
        {
            $incorrecteVelden[] = 'Antispam';
        }

        if (Request::post('lastName') === '')
        {
            $incorrecteVelden[] = 'Achternaam';
        }

        if (Request::post('initials') === '')
        {
            $incorrecteVelden[] = 'Voorletters';
        }

        if (Request::post('email') === '')
        {
            $incorrecteVelden[] = 'E-mailadres';
        }

        if (($forcedDelivery && !$memberDelivery) || (!$forcedDelivery && Request::post('delivery')))
        {
            if (Request::post('street') === '')
            {
                $incorrecteVelden[] = 'Straatnaam en huisnummer';
            }

            if (Request::post('postcode') === '')
            {
                $incorrecteVelden[] = 'Postcode';
            }

            if (Request::post('city') === '')
            {
                $incorrecteVelden[] = 'Woonplaats';
            }
        }
        return $incorrecteVelden;
    }

    /**
     * @param bool $delivery
     * @param Concert $concert
     * @param bool $memberDelivery
     * @param $deliveryMemberName
     * @param int $reserveSeats
     * @param array|null $reservedSeats
     * @param $total
     * @param $orderId
     * @param array $ticketTypes
     * @param array $orderTicketTypes
     * @param $lastName
     * @param $initials
     * @param $street
     * @param $postcode
     * @param $city
     * @param $comments
     * @param $email
     * @return bool
     */
    private function sendMail(bool $delivery, Concert $concert, bool $memberDelivery, $deliveryMemberName, int $reserveSeats, ?array $reservedSeats, float $total, $orderId, array $ticketTypes, array $orderTicketTypes, $lastName, $initials, $street, $postcode, $city, $comments, $email): bool
    {
        if ($delivery || ($concert->forcedDelivery && !$memberDelivery))
        {
            $opstuurtekst = 'naar uw adres verstuurd worden';
        }
        elseif ($concert->forcedDelivery && $memberDelivery)
        {
            $opstuurtekst = 'worden meegegeven aan ' . $deliveryMemberName;
        }
        else
        {
            $opstuurtekst = 'voor u klaargelegd worden bij de ingang van de kerk';
        }

        $voor_u_reserveerde_plaatsen = '';
        if ($reserveSeats === 1)
        {
            $voor_u_reserveerde_plaatsen = PHP_EOL . PHP_EOL . 'De volgende plaatsen zijn voor u gereserveerd: ';
            $voor_u_reserveerde_plaatsen .= implode(', ', $reservedSeats) . '.';
        }
        elseif ($reserveSeats === -1)
        {
            $voor_u_reserveerde_plaatsen = PHP_EOL . PHP_EOL . 'Er waren helaas niet voldoende plaatsen om te reserveren. De gerekende toeslag voor gereserveerde kaarten is weer van het totaalbedrag afgetrokken.';
        }

        $text = 'Hartelijk dank voor uw bestelling bij de Vlissingse Oratorium Vereniging.
Na betaling zullen uw kaarten ' . $opstuurtekst . '.' . $voor_u_reserveerde_plaatsen . '

Gebruik bij het betalen de volgende gegevens:
   Rekeningnummer: NL06INGB0000545925 t.n.v. Vlissingse Oratorium Vereniging
   Bedrag: ' . Util::formatEuro($total) . '
   Onder vermelding van: bestellingsnummer ' . $orderId . '



Hieronder volgt een overzicht van uw bestelling.

Bestellingsnummer: ' . $orderId . '

Kaartsoorten:
';
        foreach ($ticketTypes as $ticketType)
        {
            if ($orderTicketTypes[$ticketType['id']] > 0)
            {
                $text .= '   ' . $ticketType['name'] . ': ' . $orderTicketTypes[$ticketType['id']] . ' à ' . Util::formatEuro((float)$ticketType['price']) . PHP_EOL;
            }
        }
        if (!$concert->forcedDelivery)
        {
            $text .= PHP_EOL . 'Kaarten bezorgen: ' . Util::boolToText($delivery);
        }

        $text .= PHP_EOL . 'Gereserveerde plaatsen: ' . $reserveSeats === 1 ? 'Ja' : 'Nee' . PHP_EOL;
        $text .= 'Totaalbedrag: ' . Util::formatEuro($total) . '

Achternaam: ' . $lastName . '
Voorletters: ' . $initials . PHP_EOL . PHP_EOL;

        $extraFields = [
            'Straatnaam en huisnummer' => $street,
            'Postcode' => $postcode,
            'Woonplaats' => $city,
            'Opmerkingen' => $comments,
        ];

        foreach ($extraFields as $description => $contents)
        {
            if (!empty($contents))
            {
                $text .= $description . ': ' . $contents . PHP_EOL;
            }
        }

        return Util::mail($email, 'Bestelling concertkaarten', $text);
    }

    public function delete(): JsonResponse
    {
        $id = (int)Request::getVar(2);
        /** @var Order $order */
        $order = Order::loadFromDatabase($id);
        $order->delete();

        return new JsonResponse();
    }

    public function setIsPaid(): JsonResponse
    {
        $id = (int)Request::getVar(2);
        /** @var Order $order */
        $order = Order::loadFromDatabase($id);
        $order->setIsPaid();

        return new JsonResponse();
    }

    public function setIsSent(): JsonResponse
    {
        $id = (int)Request::getVar(2);
        /** @var Order $order */
        $order = Order::loadFromDatabase($id);
        $order->setIsSent();

        return new JsonResponse();
    }
}