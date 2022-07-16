<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Order;

use Cyndaron\Barcode\Barcode;
use Cyndaron\Barcode\Code128;
use Cyndaron\Barcode\Orientation;
use Cyndaron\DBAL\DBConnection;
use Cyndaron\Payment\Currency;
use Cyndaron\Payment\Payment;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\Controller;
use Cyndaron\Ticketsale\Concert;
use Cyndaron\Ticketsale\DeliveryCost\DeliveryCostInterface;
use Cyndaron\Ticketsale\InvalidOrder;
use Cyndaron\Ticketsale\TicketDelivery;
use Cyndaron\Ticketsale\TicketType;
use Cyndaron\Ticketsale\Util;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\Mail\Mail;
use Cyndaron\Util\Setting;
use Cyndaron\View\SimplePage;
use Cyndaron\View\Template\Template;
use Cyndaron\View\Template\ViewHelpers;
use Exception;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use function array_key_exists;
use function assert;
use function base64_encode;
use function file_get_contents;
use function gmdate;
use function random_int;
use function strlen;
use function strtoupper;
use function implode;
use const PHP_EOL;
use function count;
use const PUB_DIR;
use const ROOT_DIR;
use function sprintf;
use function error_log;

final class OrderController extends Controller
{
    protected array $getRoutes = [
        'checkIn' => ['level' => UserLevel::ANONYMOUS, 'function' => 'checkInGet'],
        'getTickets' => ['level' => UserLevel::ANONYMOUS, 'function' => 'getTickets'],
        'pay' => ['level' => UserLevel::ANONYMOUS, 'function' => 'pay'],
    ];

    protected array $postRoutes = [
        'add' => ['level' => UserLevel::ANONYMOUS, 'function' => 'add'],
        'checkIn' => ['level' => UserLevel::ANONYMOUS, 'function' => 'checkInPost'],
    ];

    protected array $apiPostRoutes = [
        'delete' => ['level' => UserLevel::ADMIN, 'function' => 'delete'],
        'mollieWebhook' => ['level' => UserLevel::ANONYMOUS, 'function' => 'mollieWebhook'],
        'setIsPaid' => ['level' => UserLevel::ADMIN, 'function' => 'setIsPaid'],
        'setIsSent' => ['level' => UserLevel::ADMIN, 'function' => 'setIsSent'],
    ];

    public function checkCSRFToken(string $token): bool
    {
        if ($this->action === 'checkIn')
        {
            return true;
        }
        // Mollie webhook does not need a CSRF token.
        // It only notifies us of a status change and it’s up to us to check with them what that status is.
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->action === 'mollieWebhook')
        {
            return true;
        }

        return parent::checkCSRFToken($token);
    }

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
     * @param OrderTicketTypes[] $orderTicketTypes
     * @param bool $reserveSeats
     * @param bool $wantDelivery
     * @param bool $deliveryByMember
     * @param bool $addressIsAbroad
     * @param int $postcode
     * @return OrderTotal
     */
    private function calculateTotal(
        Concert $concert,
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

        foreach ($orderTicketTypes as $orderTicketType)
        {
            // Historic, will always be 1 for new orders.
            $amount = $orderTicketType->amount;
            $ticketType = $orderTicketType->getTicketType();
            $totalPrice += $amount * $ticketType->price;
            $totalPrice += $amount * $reservedSeatCharge;
            $totalNumTickets += $amount;
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

        /** @var OrderTicketTypes[] $orderTicketTypes */
        $orderTicketTypes = [];
        $tmpOrder = new Order();
        $ticketTypes = TicketType::fetchAll(['concertId = ?'], [$concert->id], 'ORDER BY price DESC');
        foreach ($ticketTypes as $ticketType)
        {
            assert($ticketType->id !== null);
            $amount = $post->getInt('tickettype-' . $ticketType->id);
            for ($i = 0; $i < $amount; $i++)
            {
                $ott = new OrderTicketTypes();
                $ott->setTicketType($ticketType);
                $ott->secretCode = $this->generateSecretCode();

                $orderTicketTypes[] = $ott;
            }
        }

        $reserveSeats = $post->getInt('hasReservedSeats');

        $orderTotal = $this->calculateTotal(
            $concert,
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
        $order->secretCode = $this->generateSecretCode();
        $order->setAdditonalData(['donor' => $donor]);
        $result = $order->save();

        if ($result === false)
        {
            throw new InvalidOrder('Opslaan bestelling mislukt!');
        }
        /** @var int $orderId */
        $orderId = $order->id;

        foreach ($orderTicketTypes as $orderTicketType)
        {
            $orderTicketType->setOrder($order);
            $result = $orderTicketType->save();
            if ($result === false)
            {
                throw new InvalidOrder('Opslaan kaarttypen mislukt!');
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
     * @param OrderTicketTypes[] $orderTicketTypes
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
        $orderTicketTypeStats = [];
        foreach ($orderTicketTypes as $orderTicketType)
        {
            $key = $orderTicketType->tickettypeId;
            if (!array_key_exists($key, $orderTicketTypeStats))
            {
                $orderTicketTypeStats[$key] = 0;
            }

            $orderTicketTypeStats[$key]++;
        }

        $organisation = Setting::get(Setting::ORGANISATION);

        $deliveryType = $concert->getDelivery();
        if ($deliveryType === TicketDelivery::DIGITAL)
        {
            $opstuurtekst = 'per e-mail aan u opgestuurd worden';
        }
        elseif ($delivery || ($concert->forcedDelivery && !$memberDelivery))
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
        /*if ($reserveSeats === 1)
        {
            $numSeats = count($reservedSeats);
            $voor_u_reserveerde_plaatsen = PHP_EOL . PHP_EOL . "Er zijn {$numSeats} plaatsen voor u gereserveerd.";
        }
        else*/if ($reserveSeats === -1)
        {
            $voor_u_reserveerde_plaatsen = PHP_EOL . PHP_EOL . 'Er waren helaas niet voldoende plaatsen op Rang 1. De gerekende toeslag voor is weer van het totaalbedrag afgetrokken.';
        }

        $text = 'Hartelijk dank voor uw bestelling bij ' . $organisation . '.
Na betaling zullen uw kaarten ' . $opstuurtekst . '.' . $voor_u_reserveerde_plaatsen;

        if ($deliveryType !== TicketDelivery::DIGITAL)
        {
            $host = "https://{$_SERVER['HTTP_HOST']}";
            $url = "{$host}/concert-order/pay/{$orderId}";
            $text .= "U kunt betalen via deze link: {$url}

";
        }
        else
        {
            $text .= '

Gebruik bij het betalen de volgende gegevens:
   Rekeningnummer: NL06INGB0000545925 t.n.v. Vlissingse Oratorium Vereniging
   Bedrag: ' . ViewHelpers::formatEuro($total) . '
   Onder vermelding van: bestellingsnummer ' . $orderId . '

';
        }

        $text .= '
Hieronder volgt een overzicht van uw bestelling.

Bestellingsnummer: ' . $orderId . '

Kaartsoorten:
';
        foreach ($ticketTypes as $ticketType)
        {
            $numTicketsOfType = $orderTicketTypeStats[$ticketType->id] ?? 0;
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

    protected function pay(QueryBits $queryBits): Response
    {
        $orderId = $queryBits->getInt(2);
        $order = Order::loadFromDatabase($orderId);
        if ($order === null)
        {
            $page = new SimplePage('Fout', 'Order niet gevonden!');
            return new Response($page->render(), Response::HTTP_NOT_FOUND);
        }

        $concert = $order->getConcert();
        $price = $order->calculatePrice();

        $description = "Ticket(s) {$concert->name}";
        $baseUrl = "https://zeeuwsconcertkoor.nl"; //https://{$_SERVER['HTTP_HOST']}";
        $webhookUrl = "{$baseUrl}/api/concert-order/mollieWebhook";
        //$redirectUrl = "https://{$_SERVER['HTTP_HOST']}/concert-order/paid/{$order->id}/{$order->secretCode}";
        $redirectUrl = "https://zeeuwsconcertkoor.nl/concert-order/paid/{$order->id}/{$order->secretCode}";

        $payment = new Payment($description, $price, Currency::EUR, $redirectUrl, $webhookUrl);
        $molliePayment = $payment->sendToMollie();

        if (empty($molliePayment->id))
        {
            $page = new SimplePage('Fout bij inschrijven', 'Betaling niet gevonden!');
            return new Response($page->render(), Response::HTTP_NOT_FOUND);
        }


        $order->transactionCode = $molliePayment->id;
        if (!$order->save())
        {
            $page = new SimplePage('Fout bij betaling', 'Kon de betalings-ID niet opslaan!');
            return new Response($page->render(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $redirectUrl = $molliePayment->getCheckoutUrl();
        if ($redirectUrl === null)
        {
            User::addNotification('Bedankt voor je bestelling! Helaas lukte het doorsturen naar de betaalpagina niet.');
            return new RedirectResponse('/');
        }

        User::addNotification('Bedankt voor de betaling! Het kan even duren voordat deze geregistreerd is.');
        return new RedirectResponse($redirectUrl);
    }

    public function mollieWebhook(RequestParameters $post): Response
    {
        $apiKey = Setting::get('mollieApiKey');
        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($apiKey);

        $id = $post->getUnfilteredString('id');
        $payment = $mollie->payments->get($id);
        $orders = Order::fetchAll(['transactionCode = ?'], [$id]);

        if (count($orders) === 0)
        {
            $message = sprintf('Poging tot updaten van transactie met id %s mislukt.', $id);
            $message .= ' Geen orders gevonden.';

            /** @noinspection ForgottenDebugOutputInspection */
            error_log($message);
            return new JsonResponse(['error' => 'Could not find payment!'], Response::HTTP_NOT_FOUND);
        }

        $savesSucceeded = true;
        $paidStatus = false;

        if ($payment->isPaid() && !$payment->hasRefunds() && !$payment->hasChargebacks())
        {
            $paidStatus = true;
        }

        foreach ($orders as $order)
        {
            $order->isPaid = $paidStatus;
            $savesSucceeded = $savesSucceeded && $order->save();
        }

        if (!$savesSucceeded)
        {
            return new JsonResponse(['error' => 'Could not update payment information for all orders!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse();
    }

    protected function getTickets(QueryBits $queryBits): Response
    {
        $orderId = $queryBits->getInt(2);
        $order = Order::loadFromDatabase($orderId);
        if ($order === null)
        {
            $page = new SimplePage('Fout', 'Bestelling niet gevonden!');
            return new Response($page->render(), Response::HTTP_NOT_FOUND);
        }

        $secretCode = $queryBits->getString(3);
        if (!empty($order->secretCode) && $order->secretCode !== $secretCode)
        {
            $page = new SimplePage('Fout', 'Unieke code klopt niet!');
            return new Response($page->render(), Response::HTTP_FORBIDDEN);
        }

        if ($order->isPaid === false)
        {
            $page = new SimplePage('Fout', 'Bestelling is nog niet betaald!');
            return new Response($page->render(), Response::HTTP_BAD_REQUEST);
        }

        $concert = $order->getConcert();

        $pdf = new \Mpdf\Mpdf(['tempDir' => ROOT_DIR . '/cache']);

        $logoFilename = PUB_DIR . Setting::get('logo');
        $logoSrc = file_get_contents($logoFilename) ?: '';
        $organisation = Setting::get(Setting::ORGANISATION);

        foreach ($order->getTicketTypes() as $orderTicketType)
        {
            if ($orderTicketType->secretCode === null)
            {
                throw new \Exception('Geheime code niet aanwezig!');
            }
            $barcode = new Code128($orderTicketType->secretCode, 60, true, 1.5);
            $output = $barcode->getOutput();

            $ticketType = $orderTicketType->getTicketType();
            $ticketTypeDescription = $ticketType->name;
            if ($concert->hasReservedSeats)
            {
                $ticketTypeDescription .= ($order->hasReservedSeats) ? ', rang 1' : ', rang 2';
            }

            $templateVars = [
                'organisation' => $organisation,
                'concert' => $concert,
                'order' => $order,
                'ticketType' => $ticketType,
                'ticketTypeDescription' => $ticketTypeDescription,
                'orderTicketType' => $orderTicketType,
                'rawImage' => base64_encode($output),
                'rawLogo' => base64_encode($logoSrc),
            ];

            $template = new Template();
            $output = $template->render('Ticketsale/Order/Ticket', $templateVars);

            $pdf->AddPage();
            $pdf->WriteHTML($output);
        }

        $filename = "Tickets {$concert->name} {$order->initials} {$order->lastName}.pdf";
        $buffer = $pdf->Output($filename, Destination::STRING_RETURN);

        return new Response(
            $buffer,
            Response::HTTP_OK,
            [
                'Content-disposition' => 'inline; filename="' . $filename . '"',
                'Content-Type' => 'application/pdf',
                'Content-Length' => strlen($buffer),
                'Cache-Control' => 'public, must-revalidate, max-age=0',
                'Pragma' => 'public',
                'X-Generator' => 'mPDF',
                'Expires' => 'Sat, 26 Jul 1997 05:00:00 GMT',
                'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT',
            ]
        );
    }

    protected function checkInGet(): Response
    {
        return $this->checkInPage();
    }

    protected function checkInPost(RequestParameters $post): Response
    {
        $message = 'Onbekende fout!';
        $isPositive = false;
        $barcode = $post->getSimpleString('barcode');
        if (empty($barcode))
        {
            $message = 'Lege barcode!';
        }
        else
        {
            $order = Order::fetch(['secretCode = ?'], [$barcode]);
            if ($order === null)
            {
                $message = 'Geen bestelling gevonden met deze barcode!';
            }
            else
            {
                $message = 'Barcode is juist!';
                $isPositive = true;
            }
        }


        return $this->checkInPage($message, $isPositive);
    }

    private function checkInPage(?string $message = null, ?bool $isPositive = false): Response
    {
        $templateVars = [
            'message' => $message,
            'isPositive' => $isPositive,
        ];

        $template = new Template();
        $output = $template->render('Ticketsale/Order/CheckIn', $templateVars);
        return new Response($output);
    }

    private function generateSecretCode(): string
    {
        return (string)random_int(1_000_000_000, 9_999_999_999);
    }
}
