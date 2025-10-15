<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Order;

use Cyndaron\Barcode\Code128;
use Cyndaron\DBAL\Connection;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Page\SimplePage;
use Cyndaron\Payment\Currency;
use Cyndaron\Payment\Payment;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Request\UrlInfo;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Ticketsale\Concert\Concert;
use Cyndaron\Ticketsale\Concert\ConcertRepository;
use Cyndaron\Ticketsale\Concert\TicketDelivery;
use Cyndaron\Ticketsale\DeliveryCost\DeliveryCostInterface;
use Cyndaron\Ticketsale\TicketType\TicketTypeRepository;
use Cyndaron\Ticketsale\Util;
use Cyndaron\User\UserLevel;
use Cyndaron\User\UserSession;
use Cyndaron\Util\BuiltinSetting;
use Cyndaron\Util\Error\IncompleteData;
use Cyndaron\Util\MailFactory;
use Cyndaron\Util\Setting;
use Cyndaron\View\Template\TemplateRenderer;
use Exception;
use Mpdf\Output\Destination;
use Psr\Log\LoggerInterface;
use Safe\Exceptions\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use function assert;
use function base64_encode;
use function count;
use function error_log;
use function gmdate;
use function implode;
use function is_file;
use function preg_replace;
use function Safe\file_get_contents;
use function sprintf;
use function strlen;
use function strtotime;
use function strtoupper;
use const PUB_DIR;
use const CACHE_DIR;
use function range;

final class OrderController
{
    private const MAX_SECRET_CODE_RETRIES = 10;

    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
        private readonly PageRenderer $pageRenderer,
        private readonly OrderRepository $orderRepository,
        private readonly ConcertRepository $concertRepository,
        private readonly OrderTicketTypesRepository $orderTicketTypesRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[RouteAttribute('add', RequestMethod::POST, UserLevel::ANONYMOUS)]
    public function add(RequestParameters $post, UrlInfo $urlInfo, MailFactory $mailFactory, OrderConfirmationMailFactory $confirmationMailFactory, Connection $connection, TicketTypeRepository $ticketTypeRepository): Response
    {
        try
        {
            $order = $this->processOrder($post, $urlInfo, $confirmationMailFactory, $connection, $ticketTypeRepository);
            $concert = $order->concert;
            if ($order->isPaid)
            {
                $this->setOrderAsPaidAndSendMail($order, $urlInfo, $mailFactory);
                $page = new SimplePage(
                    'Bestelling verwerkt',
                    'Hartelijk dank voor uw bestelling. U ontvangt binnen enkele minuten een e-mail met een bevestiging en de tickets.',
                );
            }
            elseif ($concert->getDelivery() === TicketDelivery::DIGITAL)
            {
                $paymentLink = $this->getPaymentLink($order, $urlInfo->schemeAndHost);
                $paymentLinkText = sprintf('<br><br><a href="%s" role="button" class="btn btn-primary btn-lg">Naar de betaalomgeving</a>', $paymentLink);
                $page = new SimplePage(
                    'Bestelling betalen',
                    'Hartelijk dank voor uw bestelling. Na betaling zullen wij deze verwerken. U kunt betalen door middel van onderstaande knop.' . $paymentLinkText,
                );
            }
            else
            {
                $page = new SimplePage(
                    'Bestelling verwerkt',
                    'Hartelijk dank voor uw bestelling. U ontvangt binnen enkele minuten een e-mail met een bevestiging van uw bestelling en betaalinformatie.',
                );
            }

            return $this->pageRenderer->renderResponse($page);
        }
        catch (Exception $e)
        {
            $page = new SimplePage('Fout bij verwerken bestelling', $e->getMessage());
            return $this->pageRenderer->renderResponse($page);
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
            $qualifiesForFreeDelivery = ($addressIsAbroad) ? false : Util::postcodeQualifiesForFreeDelivery($postcode);

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
            $ticketType = $orderTicketType->ticketType;
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
     * @param UrlInfo $urlInfo
     * @param OrderConfirmationMailFactory $confirmationMailFactory
     * @param Connection $connection
     * @param TicketTypeRepository $ticketTypeRepository
     * @throws InvalidOrder
     * @throws JsonException
     * @return Order
     */
    private function processOrder(RequestParameters $post, UrlInfo $urlInfo, OrderConfirmationMailFactory $confirmationMailFactory, Connection $connection, TicketTypeRepository $ticketTypeRepository): Order
    {
        if ($post->isEmpty())
        {
            throw new InvalidOrder('De bestellingsgegevens zijn niet goed aangekomen.');
        }

        $concertId = $post->getInt('concert_id');

        $concert = $this->concertRepository->fetchById($concertId);
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
        $ticketTypes = $ticketTypeRepository->fetchByConcertAndSortByPrice($concert);

        foreach ($ticketTypes as $ticketType)
        {
            assert($ticketType->id !== null);
            $amount = $post->getInt('tickettype-' . $ticketType->id);
            for ($i = 0; $i < $amount; $i++)
            {
                $ott = new OrderTicketTypes();
                $ott->ticketType = $ticketType;

                $orderTicketTypes[] = $ott;
            }
        }

        $reserveSeats = OrderReserveSeats::tryFrom($post->getInt('hasReservedSeats')) ?? OrderReserveSeats::NOT_RESERVED;

        $orderTotal = $this->calculateTotal(
            $concert,
            $orderTicketTypes,
            $reserveSeats === OrderReserveSeats::RESERVE,
            $post->getBool('bezorgen'),
            $deliveryByMember,
            $addressIsAbroad,
            (int)$postcode
        );

        $totalAmount = $orderTotal->amount;
        $totalNumTickets = $orderTotal->numTickets;
        $payForDelivery = $orderTotal->payForDelivery;

        $email = $post->getEmail('email');
        $lastName = $post->getSimpleString('lastName');
        $initials = $post->getInitials('initials');
        $street = $post->getSimpleString('street');
        $postcode = $post->getPostcode('postcode');
        $city = $post->getSimpleString('city');
        $comments = $post->getSimpleString('comments');

        $order = new Order();
        $order->concert = $concert;
        $order->lastName = $lastName;
        $order->initials = $initials;
        $order->email = $email;
        $order->street = $street;
        $order->houseNumber = 0;
        $order->postcode = $postcode;
        $order->city = $city;
        $order->delivery = $payForDelivery;
        $order->hasReservedSeats = ($reserveSeats === OrderReserveSeats::RESERVE);
        $order->deliveryByMember = $deliveryByMember;
        $order->deliveryMemberName = $deliveryMemberName;
        $order->addressIsAbroad = $addressIsAbroad;
        $order->comments = $comments;
        $order->isPaid = $orderTotal->amount === 0.00;
        $order->setAdditonalData([
            'donor' => $post->getBool('donor'),
            'subscribeToNewsletter' => $post->getBool('subscribeToNewsletter')
        ]);

        $saveResult = false;
        $lastError = null;
        for ($i = 0; $i < self::MAX_SECRET_CODE_RETRIES; $i++)
        {
            $order->secretCode = Util::generateSecretCode();
            try
            {
                $this->orderRepository->save($order);
                $saveResult = true;
            }
            catch (\Throwable $t)
            {
                $lastError = $t;
            }
        }

        if ($saveResult === false)
        {
            throw new InvalidOrder('Opslaan bestelling mislukt!');
        }
        if ($lastError !== null)
        {
            $this->logger->error((string)$lastError);
        }

        /** @var int $orderId */
        $orderId = $order->id;

        foreach ($orderTicketTypes as $orderTicketType)
        {
            $orderTicketType->order = $order;
            $saveResult = false;
            for ($i = 0; $i < self::MAX_SECRET_CODE_RETRIES; $i++)
            {
                $orderTicketType->secretCode = Util::generateSecretCode();
                try
                {
                    $this->orderTicketTypesRepository->save($orderTicketType);
                    $saveResult = true;
                    break;
                }
                catch (\Throwable)
                {
                }
            }

            if ($saveResult === false)
            {
                throw new InvalidOrder('Opslaan kaarttypen mislukt!');
            }
        }

        if ($reserveSeats === OrderReserveSeats::RESERVE)
        {
            $reservedSeats = $this->reserveSeats($connection, $concert, $orderId, $totalNumTickets);
            if ($reservedSeats === null)
            {
                $connection->executeQuery('UPDATE ticketsale_orders SET hasReservedSeats = 0 WHERE id=?', [$orderId]);
                $totalAmount -= $totalNumTickets * $concert->reservedSeatCharge;
                $reserveSeats = OrderReserveSeats::FAILED_RESERVE;
            }
        }

        if (!$order->isPaid)
        {
            $paymentLink = $this->getPaymentLink($order, $urlInfo->schemeAndHost);
            $confirmationMail = $confirmationMailFactory->create($order, $concert, $reserveSeats, $totalAmount, $ticketTypes, $orderTicketTypes, $paymentLink);
            $confirmationMail->send();
        }

        return $order;
    }

    /**
     * @param bool $forcedDelivery
     * @param bool $memberDelivery
     * @param RequestParameters $post
     * @return string[]
     */
    private function checkForm(bool $forcedDelivery, bool $memberDelivery, RequestParameters $post): array
    {
        $incorrectFields = [];
        if (strtoupper($post->getAlphaNum('antispam')) !== 'VLISSINGEN')
        {
            $incorrectFields[] = 'Antispam';
        }

        if ($post->getSimpleString('lastName') === '')
        {
            $incorrectFields[] = 'Achternaam';
        }

        if ($post->getInitials('initials') === '')
        {
            $incorrectFields[] = 'Voorletters';
        }

        if ($post->getEmail('email') === '')
        {
            $incorrectFields[] = 'E-mailadres';
        }

        if (($forcedDelivery && !$memberDelivery) || (!$forcedDelivery && $post->getBool('delivery')))
        {
            if ($post->getSimpleString('street') === '')
            {
                $incorrectFields[] = 'Straatnaam en huisnummer';
            }

            if ($post->getPostcode('postcode') === '')
            {
                $incorrectFields[] = 'Postcode';
            }

            if ($post->getSimpleString('city') === '')
            {
                $incorrectFields[] = 'Woonplaats';
            }
        }
        return $incorrectFields;
    }

    #[RouteAttribute('delete', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function delete(QueryBits $queryBits): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $this->orderRepository->deleteById($id);

        return new JsonResponse();
    }

    private function setOrderAsPaidAndSendMail(Order $order, UrlInfo $urlInfo, MailFactory $mailFactory): void
    {
        $order->isPaid = true;
        $this->orderRepository->save($order);
        $concert = $order->concert;
        $organisation = Setting::get(BuiltinSetting::ORGANISATION);

        $text = "Hartelijk dank voor uw bestelling bij {$organisation}. Wij hebben uw betaling in goede orde ontvangen.\n";
        $ticketDelivery = $concert->getDelivery();
        if ($ticketDelivery === TicketDelivery::DIGITAL)
        {
            $url = $this->getLinkToTickets($order, $urlInfo->schemeAndHost);
            $text .= "U kunt uw kaarten hier downloaden: {$url}\n\n";
            $text .= "Wij verzoeken u het ticket te downloaden vóórdat u de kerk binnengaat en het originele ticket te tonen. ";
            $text .= "Screenshots van de tickets kunnen wij niet goed scannen.\nDit om wachttijd te voorkomen.";
        }
        elseif ($order->delivery || ($concert->forcedDelivery && !$order->deliveryByMember))
        {
            $text .= 'Uw kaarten zullen zo spoedig mogelijk worden opgestuurd.';
        }
        elseif ($concert->forcedDelivery && $order->deliveryByMember)
        {
            $text .= 'Uw kaarten zullen worden meegegeven aan ' . $order->deliveryMemberName . '.';
        }
        else
        {
            $text .= 'Uw kaarten zullen op de avond van het concert voor u klaarliggen bij de kassa.';
        }

        $mail = $mailFactory->createMailWithDefaults(
            new Address($order->email),
            'Betalingsbevestiging',
            $text
        );
        $mail->send();
    }

    #[RouteAttribute('setIsPaid', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function setIsPaid(QueryBits $queryBits, UrlInfo $urlInfo, MailFactory $mailFactory): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        /** @var Order $order */
        $order = $this->orderRepository->fetchById($id);
        $this->setOrderAsPaidAndSendMail($order, $urlInfo, $mailFactory);

        return new JsonResponse();
    }

    #[RouteAttribute('setIsSent', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function setIsSent(QueryBits $queryBits): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        /** @var Order $order */
        $order = $this->orderRepository->fetchById($id);
        $order->isDelivered = true;
        $this->orderRepository->save($order);

        return new JsonResponse();
    }

    #[RouteAttribute('pay', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function pay(QueryBits $queryBits, Request $request, UserSession $userSession, OrderHelper $orderHelper): Response
    {
        $orderId = $queryBits->getInt(2);
        $order = $this->orderRepository->fetchById($orderId);
        if ($order === null)
        {
            $page = new SimplePage('Fout', 'Order niet gevonden!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_NOT_FOUND);
        }

        if ($order->isPaid)
        {
            $page = new SimplePage(
                'Betalen',
                'Deze order is al betaald! Check uw e-mail voor de betalingsbevestiging, hierin zitten uw kaartjes.'
            );
            return $this->pageRenderer->renderResponse($page);
        }
        if (!empty($order->transactionCode) && strtotime($order->modified->format('U')) > strtotime('-30 minutes'))
        {
            $page = new SimplePage('Betalen', 'Er loopt al een betaling voor deze order. Wacht 30 minuten om het opnieuw te proberen.');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_BAD_REQUEST);
        }

        $concert = $order->concert;
        $price = $orderHelper->calculateOrderTotal($order);

        $description = "Ticket(s) {$concert->name}";
        $baseUrl = $request->getSchemeAndHttpHost();
        $webhookUrl = "{$baseUrl}/api/concert-order/mollieWebhook";
        $redirectUrl = "{$baseUrl}/concert-order/afterPayment/{$order->id}/{$order->secretCode}";

        $payment = new Payment($description, $price, Currency::EUR, $redirectUrl, $webhookUrl);
        $molliePayment = $payment->sendToMollie();

        if (empty($molliePayment->id))
        {
            $page = new SimplePage('Fout bij inschrijven', 'Betaling niet gevonden!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_NOT_FOUND);
        }

        $order->transactionCode = $molliePayment->id;
        try
        {
            $this->orderRepository->save($order);
        }
        catch (\Throwable)
        {
            $page = new SimplePage('Fout bij betaling', 'Kon de betalings-ID niet opslaan!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $redirectUrl = $molliePayment->getCheckoutUrl();
        if ($redirectUrl === null)
        {
            $userSession->addNotification('Bedankt voor je bestelling! Helaas lukte het doorsturen naar de betaalpagina niet.');
            return new RedirectResponse('/');
        }

        $userSession->addNotification('Bedankt voor de betaling! Het kan even duren voordat deze geregistreerd is.');
        return new RedirectResponse($redirectUrl);
    }

    #[RouteAttribute('mollieWebhook', RequestMethod::POST, UserLevel::ANONYMOUS, isApiMethod: true, skipCSRFCheck: true)]
    public function mollieWebhook(RequestParameters $post, UrlInfo $urlInfo, MailFactory $mailFactory): Response
    {
        $apiKey = Setting::get('mollieApiKey');
        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($apiKey);

        $id = $post->getUnfilteredString('id');
        $payment = $mollie->payments->get($id);
        $orders = $this->orderRepository->fetchAll(['transactionCode = ?'], [$id]);

        if (count($orders) === 0)
        {
            $message = sprintf('Poging tot updaten van transactie met id %s mislukt.', $id);
            $message .= ' Geen orders gevonden.';

            /** @noinspection ForgottenDebugOutputInspection */
            error_log($message);
            return new JsonResponse(['error' => 'Could not find payment!'], Response::HTTP_NOT_FOUND);
        }

        $paidStatus = false;

        if ($payment->isPaid() && !$payment->hasRefunds() && !$payment->hasChargebacks())
        {
            $paidStatus = true;
        }

        try
        {
            foreach ($orders as $order)
            {
                if ($paidStatus)
                {
                    $this->setOrderAsPaidAndSendMail($order, $urlInfo, $mailFactory);
                }
                else
                {
                    $order->isPaid = false;
                }
                $this->orderRepository->save($order);
            }
        }
        catch (\Throwable)
        {
            return new JsonResponse(['error' => 'Could not update payment information for all orders!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        return new JsonResponse();
    }

    #[RouteAttribute('getTickets', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function getTickets(QueryBits $queryBits): Response
    {
        $orderId = $queryBits->getInt(2);
        $order = $this->orderRepository->fetchById($orderId);
        if ($order === null)
        {
            $page = new SimplePage('Fout', 'Bestelling niet gevonden!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_NOT_FOUND);
        }

        $secretCode = $queryBits->getString(3);
        if (empty($order->secretCode) || $order->secretCode !== $secretCode)
        {
            $page = new SimplePage('Fout', 'Geheime code klopt niet!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_FORBIDDEN);
        }

        if ($order->isPaid === false)
        {
            $page = new SimplePage('Fout', 'Bestelling is nog niet betaald!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_PAYMENT_REQUIRED);
        }

        $concert = $order->concert;

        $pdf = new \Mpdf\Mpdf(['tempDir' => CACHE_DIR]);

        $logoFilename = PUB_DIR . Setting::get('logo');
        $logoSrc = is_file($logoFilename) ? file_get_contents($logoFilename) : '';
        $organisation = Setting::get(BuiltinSetting::ORGANISATION);

        foreach ($this->orderTicketTypesRepository->fetchAllByOrder($order) as $orderTicketType)
        {
            if ($orderTicketType->secretCode === null)
            {
                throw new Exception('Geheime code niet aanwezig!');
            }
            $barcode = new Code128($orderTicketType->secretCode, 60, true, 1.5);
            $output = $barcode->getOutput();

            $ticketType = $orderTicketType->ticketType;
            $ticketTypeDescription = $ticketType->name;
            if ($concert->hasReservedSeats)
            {
                $ticketTypeDescription .= ($order->hasReservedSeats) ? ', rang 1' : ', rang 2';
            }

            $templateVars = [
                'organisation' => $organisation,
                'concert' => $concert,
                'location' => $concert->location,
                'order' => $order,
                'ticketType' => $ticketType,
                'ticketTypeDescription' => $ticketTypeDescription,
                'orderTicketType' => $orderTicketType,
                'rawImage' => base64_encode($output),
                'rawLogo' => base64_encode($logoSrc),
            ];

            $output = $this->templateRenderer->render('Ticketsale/Order/Ticket', $templateVars);

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

    #[RouteAttribute('checkIn', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function checkInGet(QueryBits $queryBits): Response
    {
        $concertId = $queryBits->getInt(2);
        $concert = $this->concertRepository->fetchById($concertId);
        if ($concert === null)
        {
            throw new Exception('Concert niet gevonden!');
        }
        $secretCode = $queryBits->getString(3);
        if ($secretCode !== $concert->secretCode)
        {
            throw new Exception('Geheime code klopt niet!');
        }

        return $this->checkInPage($concert);
    }

    /**
     * @param RequestParameters $post
     * @param Concert $concert
     * @return array{0: bool, 1: string}
     */
    private function checkScannedBarcode(RequestParameters $post, Concert $concert): array
    {
        $barcode = $post->getSimpleString('barcode');
        $barcode = preg_replace('/[^0-9]+/', '', $barcode);
        if (empty($barcode))
        {
            return [false, 'Lege barcode!'];
        }

        $ticket = $this->orderTicketTypesRepository->fetch(['secretCode = ?'], [$barcode]);
        if ($ticket === null)
        {
            return [false, 'Geen kaartje gevonden met deze barcode!'];
        }

        $order = $ticket->order;
        if (!$order->isPaid)
        {
            return [false, 'Bestelling is niet betaald!'];
        }

        if ($order->concert->id !== $concert->id)
        {
            return [false, 'Dit kaartje is voor een ander concert!'];
        }

        if ($ticket->hasBeenScanned)
        {
            return [false, 'Dit kaartje is al gescand!'];
        }

        $ticket->hasBeenScanned = true;
        $this->orderTicketTypesRepository->save($ticket);

        return [true, 'Barcode is juist!'];
    }

    #[RouteAttribute('checkIn', RequestMethod::POST, UserLevel::ANONYMOUS, skipCSRFCheck: true)]
    public function checkInPost(QueryBits $queryBits, RequestParameters $post): Response
    {
        $concertId = $queryBits->getInt(2);
        $concert = $this->concertRepository->fetchById($concertId);
        if ($concert === null)
        {
            throw new Exception('Concert niet gevonden!');
        }
        $secretCode = $queryBits->getString(3);
        if ($secretCode !== $concert->secretCode)
        {
            throw new Exception('Geheime code klopt niet!');
        }

        [$isCorrect, $message] = $this->checkScannedBarcode($post, $concert);

        return $this->checkInPage($concert, $message, $isCorrect);
    }

    private function checkInPage(Concert $concert, string|null $message = null, bool|null $isPositive = false): Response
    {
        $templateVars = [
            'concert' => $concert,
            'message' => $message,
            'isPositive' => $isPositive,
            'nonce' => \Cyndaron\Routing\Kernel::getScriptNonce(),
        ];

        $output = $this->templateRenderer->render('Ticketsale/Order/CheckInPage', $templateVars);
        return new Response($output);
    }

    #[RouteAttribute('afterPayment', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function afterPayment(QueryBits $queryBits, Request $request): Response
    {
        $baseUrl = $request->getSchemeAndHttpHost();
        if ($queryBits->hasIndex(3))
        {
            $orderId = $queryBits->getInt(2);
            $order = $this->orderRepository->fetchById($orderId);
            if ($order !== null && $order->isPaid)
            {
                $secretCode = $queryBits->getString(3);
                if (!empty($order->secretCode) && $order->secretCode === $secretCode)
                {
                    $page = new SimplePage(
                        'Bestelling verwerkt',
                        sprintf('Hartelijk dank voor uw betaling.<br><br><a href="%s" role="button" class="btn btn-primary" target="_blank">Tickets ophalen</a>', $this->getLinkToTickets($order, $baseUrl)),
                    );
                    return $this->pageRenderer->renderResponse($page);
                }
            }
        }

        $page = new SimplePage(
            'Bestelling verwerkt',
            'Hartelijk dank voor uw bestelling. Als de betaling is gelukt, ontvangt binnen enkele minuten een e-mail met een link om de kaartjes te downloaden.',
        );
        return $this->pageRenderer->renderResponse($page);
    }

    private function getLinkToTickets(Order $order, string $baseUrl): string
    {
        return "{$baseUrl}/concert-order/getTickets/{$order->id}/{$order->secretCode}";
    }

    private function getPaymentLink(Order $order, string $baseUrl): string
    {
        assert($order->id !== null);
        return "{$baseUrl}/concert-order/pay/{$order->id}";
    }

    /**
     * @param Connection $connection
     * @param Concert $concert
     * @param int $orderId
     * @param int $numTickets
     * @return int[]|null Which seats were reserved, if there were enough, null otherwise
     */
    private function reserveSeats(Connection $connection, Concert $concert, int $orderId, int $numTickets):array|null
    {
        if (!$concert->id)
        {
            throw new IncompleteData('No ID!');
        }

        $foundEnoughSeats = false;
        $reservedSeats = [];

        $reservedSeatsPerOrder = $connection->doQueryAndFetchAll('SELECT * FROM ticketsale_reservedseats WHERE orderId IN (SELECT id FROM ticketsale_orders WHERE concertId=?)', [$concert->id]) ?: [];
        foreach ($reservedSeatsPerOrder as $reservedSeatsForThisOrder)
        {
            for ($i = $reservedSeatsForThisOrder['firstSeat']; $i <= $reservedSeatsForThisOrder['lastSeat']; $i++)
            {
                $reservedSeats[$i] = true;
            }
        }

        $firstSeat = 0;
        $lastSeat = 0;

        $adjacentFreeSeats = 0;
        for ($stoel = 1; $stoel <= $concert->numReservedSeats; $stoel++)
        {
            if (($reservedSeats[$stoel] ?? false) === true)
            {
                $adjacentFreeSeats = 0;
            }
            else
            {
                $adjacentFreeSeats++;
            }

            if ($adjacentFreeSeats === $numTickets)
            {
                $foundEnoughSeats = true;
                $firstSeat = $stoel - $numTickets + 1;
                $lastSeat = $stoel;
                break;
            }
        }

        if ($foundEnoughSeats)
        {
            $connection->executeQuery('INSERT INTO ticketsale_reservedseats(`orderId`, `row`, `firstSeat`, `lastSeat`) VALUES(?, \'A\', ?, ?)', [$orderId, $firstSeat, $lastSeat]);
            return range($firstSeat, $lastSeat);
        }

        return null;
    }
}
