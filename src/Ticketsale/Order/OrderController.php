<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Order;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\Controller;
use Cyndaron\Ticketsale\Concert;
use Cyndaron\Ticketsale\DeliveryCost\DeliveryCostInterface;
use Cyndaron\Ticketsale\InvalidOrder;
use Cyndaron\Ticketsale\Order\Order;
use Cyndaron\Ticketsale\TicketType;
use Cyndaron\Ticketsale\Util;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\Mail\Mail;
use Cyndaron\View\SimplePage;
use Cyndaron\View\Template\ViewHelpers;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use function assert;
use function strtoupper;
use function implode;
use const PHP_EOL;

final class OrderController extends Controller
{
    protected array $postRoutes = [
        'add' => ['level' => UserLevel::ANONYMOUS, 'function' => 'add'],
    ];

    protected array $apiPostRoutes = [
        'delete' => ['level' => UserLevel::ADMIN, 'function' => 'delete'],
        'setIsPaid' => ['level' => UserLevel::ADMIN, 'function' => 'setIsPaid'],
        'setIsSent' => ['level' => UserLevel::ADMIN, 'function' => 'setIsSent'],
    ];

    protected function add(RequestParameters $post): Response
    {
        try
        {
            $this->processOrder($post);

            $page = new SimplePage(
                'Bestelling verwerkt',
                'Hartelijk dank voor uw bestelling. U ontvangt binnen enkele minuten een e-mail met een bevestiging van uw bestelling en betaalinformatie.'
            );
            return new Response($page->render());
        }
        catch (Exception $e)
        {
            $page = new SimplePage('Fout bij verwerken bestelling', $e->getMessage());
            return new Response($page->render());
        }
    }

    /**
     * @param Concert $concert
     * @param array $ticketTypes
     * @param OrderTicketType[] $orderTicketTypes
     * @param bool $reserveSeats
     * @param bool $wantDelivery
     * @param bool $deliveryByMember
     * @param bool $addressIsAbroad
     * @param int $postcode
     * @return \Cyndaron\Ticketsale\Order\OrderTotal
     */
    private function calculateTotal(
        Concert $concert,
        array $ticketTypes,
        array $orderTicketTypes,
        bool $reserveSeats,
        bool $wantDelivery,
        bool &$deliveryByMember,
        bool $addressIsAbroad,
        int $postcode
    ): OrderTotal {
        $totalPrice = 0.0;
        $totalNumTickets = 0;

        if ($concert->forcedDelivery)
        {
            $qualifiesForFreeDelivery = ($addressIsAbroad) ? false : Util::postcodeQualifiesForFreeDelivery((int)$postcode);

            if ($qualifiesForFreeDelivery)
            {
                $payForDelivery = false;
                $deliveryByMember = false;
            }
            elseif ($deliveryByMember)
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
            $payForDelivery = $wantDelivery;
        }

        $reservedSeatCharge = $reserveSeats ? $concert->reservedSeatCharge : 0;
        foreach ($ticketTypes as $ticketType)
        {
            assert($ticketType->id !== null);
            $ott = $orderTicketTypes[$ticketType->id] ?? null;
            if ($ott === null)
            {
                continue;
            }

            $totalPrice += $ott->amount * $ticketType->price;
            $totalPrice += $ott->amount * $reservedSeatCharge;
            $totalNumTickets += $ott->amount;
        }

        $deliveryCostInterface = $concert->getDeliveryCostInterface();
        $tempOrder = new Order();
        $tempOrder->delivery = $payForDelivery;
        /** @var DeliveryCostInterface $deliveryCost */
        $deliveryCost = new $deliveryCostInterface($concert, $tempOrder, $orderTicketTypes);

        $totalPrice += $deliveryCost->getCost();

        $orderTotal = new OrderTotal();
        $orderTotal->amount = $totalPrice;
        $orderTotal->numTickets = $totalNumTickets;
        $orderTotal->ticketTypes = $orderTicketTypes;
        $orderTotal->payForDelivery = $payForDelivery;

        return $orderTotal;
    }

    /**
     * @param RequestParameters $post
     * @throws InvalidOrder
     */
    private function processOrder(RequestParameters $post): void
    {
        if ($post->isEmpty())
        {
            throw new InvalidOrder('De bestellingsgegevens zijn niet goed aangekomen.');
        }

        $concertId = $post->getInt('concert_id');

        $concert = Concert::loadFromDatabase($concertId);
        if ($concert === null)
        {
            throw new InvalidOrder('Concert niet gevonden!');
        }

        if (!$concert->openForSales)
        {
            throw new InvalidOrder('De verkoop voor dit concert is helaas gesloten, u kunt geen kaarten meer bestellen.');
        }

        $postcode = $post->getPostcode('postcode');
        $addressIsAbroad = $post->getUnfilteredString('country') === 'abroad';
        $deliveryByMember = $post->getBool('deliveryByMember');
        $deliveryByMember = $addressIsAbroad ? true : $deliveryByMember;
        $deliveryMemberName = $post->getSimpleString('deliveryMemberName');

        $incorrecteVelden = $this->checkForm($concert->forcedDelivery, $deliveryByMember, $post);
        if (!empty($incorrecteVelden))
        {
            $message = 'De volgende velden zijn niet goed ingevuld of niet goed aangekomen: ';
            $message .= implode(', ', $incorrecteVelden) . '.';
            throw new InvalidOrder($message);
        }

        /** @var OrderTicketType[] $orderTicketTypes */
        $orderTicketTypes = [];
        $tmpOrder = new Order();
        $ticketTypes = TicketType::fetchAll(['concertId = ?'], [$concert->id], 'ORDER BY price DESC');
        foreach ($ticketTypes as $ticketType)
        {
            assert($ticketType->id !== null);
            $amount = $post->getInt('tickettype-' . $ticketType->id);
            $ott = new OrderTicketType();
            $ott->order = $tmpOrder;
            $ott->amount = $amount;
            $ott->ticketType = $ticketType;
            $orderTicketTypes[$ticketType->id] = $ott;
        }

        $reserveSeats = $post->getInt('hasReservedSeats');

        $orderTotal = $this->calculateTotal(
            $concert,
            $ticketTypes,
            $orderTicketTypes,
            $reserveSeats === 1,
            $post->getBool('bezorgen'),
            $deliveryByMember,
            $addressIsAbroad,
            (int)$postcode
        );

        $totalAmount = $orderTotal->amount;
        $totalNumTickets = $orderTotal->numTickets;
        $payForDelivery = $orderTotal->payForDelivery;

        if ($totalAmount <= 0)
        {
            throw new InvalidOrder('U heeft een bestelling van 0 kaarten geplaatst of het formulier is niet goed aangekomen.');
        }

        $email = $post->getEmail('email');
        $lastName = $post->getSimpleString('lastName');
        $initials = $post->getInitials('initials');
        $street = $post->getSimpleString('street');
        $postcode = $post->getPostcode('postcode');
        $city = $post->getSimpleString('city');
        $donor = $post->getBool('donor');
        $comments = $post->getSimpleString('comments');

        $order = new Order();
        $order->concertId = $concertId;
        $order->lastName = $lastName;
        $order->initials = $initials;
        $order->email = $email;
        $order->street = $street;
        $order->houseNumber = 0;
        $order->postcode = $postcode;
        $order->city = $city;
        $order->delivery = $payForDelivery;
        $order->hasReservedSeats = ($reserveSeats === 1);
        $order->deliveryByMember = $deliveryByMember;
        $order->deliveryMemberName = $deliveryMemberName;
        $order->addressIsAbroad = $addressIsAbroad;
        $order->comments = $comments;
        $order->setAdditonalData(['donor' => $donor]);
        $result = $order->save();

        if ($result === false)
        {
            throw new InvalidOrder('Opslaan bestelling mislukt!');
        }
        /** @var int $orderId */
        $orderId = $order->id;

        foreach ($ticketTypes as $ticketType)
        {
            $ott = $orderTicketTypes[$ticketType->id] ?? null;
            $numTicketsOfType = $ott->amount ?? 0;
            if ($numTicketsOfType > 0)
            {
                $result = DBConnection::doQuery(
                    'INSERT INTO ticketsale_orders_tickettypes(`orderId`, `tickettypeId`, `amount`) VALUES(?, ?, ?)',
                    [$orderId, $ticketType->id, $numTicketsOfType]
                );
                if ($result === false)
                {
                    throw new InvalidOrder('Opslaan kaarttypen mislukt!');
                }
            }
        }

        $reservedSeats = null;
        if ($reserveSeats === 1)
        {
            $reservedSeats = $concert->reserveSeats($orderId, $totalNumTickets);
            if ($reservedSeats === null)
            {
                DBConnection::doQuery('UPDATE ticketsale_orders SET hasReservedSeats = 0 WHERE id=?', [$orderId]);
                $totalAmount -= $totalNumTickets * $concert->reservedSeatCharge;
                $reserveSeats = -1;
            }
        }

        $this->sendMail($payForDelivery, $concert, $deliveryByMember, $deliveryMemberName, $reserveSeats, $reservedSeats ?: [], $totalAmount, $orderId, $ticketTypes, $orderTicketTypes, $lastName, $initials, $street, $postcode, $city, $comments, $email);
    }

    private function checkForm(bool $forcedDelivery, bool $memberDelivery, RequestParameters $post): array
    {
        $incorrecteVelden = [];
        if (strtoupper($post->getAlphaNum('antispam')) !== 'VLISSINGEN')
        {
            $incorrecteVelden[] = 'Antispam';
        }

        if ($post->getSimpleString('lastName') === '')
        {
            $incorrecteVelden[] = 'Achternaam';
        }

        if ($post->getInitials('initials') === '')
        {
            $incorrecteVelden[] = 'Voorletters';
        }

        if ($post->getEmail('email') === '')
        {
            $incorrecteVelden[] = 'E-mailadres';
        }

        if (($forcedDelivery && !$memberDelivery) || (!$forcedDelivery && $post->getBool('delivery')))
        {
            if ($post->getSimpleString('street') === '')
            {
                $incorrecteVelden[] = 'Straatnaam en huisnummer';
            }

            if ($post->getPostcode('postcode') === '')
            {
                $incorrecteVelden[] = 'Postcode';
            }

            if ($post->getSimpleString('city') === '')
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
     * @param string $deliveryMemberName
     * @param int $reserveSeats
     * @param array $reservedSeats
     * @param float $total
     * @param int $orderId
     * @param TicketType[] $ticketTypes
     * @param OrderTicketType[] $orderTicketTypes
     * @param string $lastName
     * @param string $initials
     * @param string $street
     * @param string $postcode
     * @param string $city
     * @param string $comments
     * @param string $email
     * @return bool
     */
    private function sendMail(bool $delivery, Concert $concert, bool $memberDelivery, string $deliveryMemberName, int $reserveSeats, array $reservedSeats, float $total, int $orderId, array $ticketTypes, array $orderTicketTypes, string $lastName, string $initials, string $street, string $postcode, string $city, string $comments, string $email): bool
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
   Bedrag: ' . ViewHelpers::formatEuro($total) . '
   Onder vermelding van: bestellingsnummer ' . $orderId . '



Hieronder volgt een overzicht van uw bestelling.

Bestellingsnummer: ' . $orderId . '

Kaartsoorten:
';
        foreach ($ticketTypes as $ticketType)
        {
            $ott = $orderTicketTypes[$ticketType->id] ?? null;
            $numTicketsOfType = $ott->amount ?? 0;
            if ($numTicketsOfType > 0)
            {
                $text .= '   ' . $ticketType->name . ': ' . $numTicketsOfType . ' à ' . ViewHelpers::formatEuro($ticketType->price) . PHP_EOL;
            }
        }
        if (!$concert->forcedDelivery)
        {
            $text .= PHP_EOL . 'Kaarten bezorgen: ' . ViewHelpers::boolToText($delivery);
        }

        $text .= PHP_EOL . 'Gereserveerde plaatsen: ' . ($reserveSeats === 1 ? 'Ja' : 'Nee') . PHP_EOL;
        $text .= 'Totaalbedrag: ' . ViewHelpers::formatEuro($total) . '

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

        $mail = new Mail(new Address($email), 'Bestelling concertkaarten', $text);
        return $mail->send();
    }

    public function delete(QueryBits $queryBits): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        /** @var Order $order */
        $order = Order::loadFromDatabase($id);
        $order->delete();

        return new JsonResponse();
    }

    public function setIsPaid(QueryBits $queryBits): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        /** @var Order $order */
        $order = Order::loadFromDatabase($id);
        $order->setIsPaid();

        return new JsonResponse();
    }

    public function setIsSent(QueryBits $queryBits): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        /** @var Order $order */
        $order = Order::loadFromDatabase($id);
        $order->setIsSent();

        return new JsonResponse();
    }
}
