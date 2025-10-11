<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout\Ticket;

use Cyndaron\Error\ErrorPage;
use Cyndaron\Geelhoed\Tryout\Tryout;
use Cyndaron\Geelhoed\Tryout\TryoutRepository;
use Cyndaron\Page\Page;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Page\SimplePage;
use Cyndaron\Payment\Currency;
use Cyndaron\Payment\Payment;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Request\UrlInfo;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Ticketsale\Order\InvalidOrder;
use Cyndaron\User\UserLevel;
use Cyndaron\User\UserSession;
use Cyndaron\Util\BuiltinSetting;
use Cyndaron\Util\MailFactory;
use Cyndaron\Util\RuntimeUserSafeError;
use Cyndaron\Util\SettingsRepository;
use Cyndaron\Util\Util;
use Cyndaron\View\Template\ViewHelpers;
use DateInterval;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use function assert;
use function strtotime;
use function count;
use function sprintf;
use function error_log;

final class Controller
{
    public const RIGHT_MANAGE = 'tryoutorders_edit';

    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly SettingsRepository $settingsRepository,
        private readonly TryoutRepository $tryoutRepository,
        private readonly TypeRepository $typeRepository,
        private readonly OrderRepository $orderRepository,
        private readonly OrderTicketTypeRepository $ottRepository,
    ) {
    }

    #[RouteAttribute('bestellen', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function showOrderForm(QueryBits $queryBits): Response
    {
        $tryout = $this->getRequestedOrCurrentTryout($queryBits->getInt(2));
        $now = new \DateTimeImmutable();
        if ($now > $tryout->end)
        {
            return $this->pageRenderer->renderErrorResponse(new ErrorPage('Kaartverkoop gesloten', 'Dit toernooi is al afgelopen!'));
        }

        $ticketTypes = $this->typeRepository->fetchAll();

        $date = ViewHelpers::filterDutchDate($tryout->start);
        $page = new Page();
        $page->title = "Kaartverkoop Tryout {$date}";
        $page->template = 'Geelhoed/Tryout/Ticket/OrderTicketsPage';
        $page->addScript('/src/Geelhoed/Tryout/Ticket/js/OrderTicketsPage.js');
        $page->addCss('/src/Ticketsale/css/Ticketsale.min.css');
        $page->addTemplateVars([
            'event' => $tryout,
            'ticketTypes' => $ticketTypes,
        ]);

        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('order', RequestMethod::POST, UserLevel::ANONYMOUS)]
    public function add(RequestParameters $post, UrlInfo $urlInfo, MailFactory $mailFactory): Response
    {
        try
        {
            $order = $this->processOrder($post, $urlInfo, $mailFactory);

            $paymentLink = $this->getPaymentLink($order, $urlInfo->schemeAndHost);
            $paymentLinkText = sprintf('<br><br><a href="%s" role="button" class="btn btn-primary btn-lg">Naar de betaalomgeving</a>', $paymentLink);
            $page = new SimplePage(
                'Bestelling betalen',
                'Hartelijk dank voor uw bestelling. Na betaling zullen wij deze verwerken. U kunt betalen door middel van onderstaande knop.' . $paymentLinkText,
            );

            return $this->pageRenderer->renderResponse($page);
        }
        catch (Exception $e)
        {
            $page = new SimplePage('Fout bij verwerken bestelling', $e->getMessage());
            return $this->pageRenderer->renderResponse($page);
        }
    }

    private function processOrder(RequestParameters $post, UrlInfo $urlInfo, MailFactory $mailFactory): Order
    {
        if ($post->isEmpty())
        {
            throw new InvalidOrder('De bestellingsgegevens zijn niet goed aangekomen.');
        }

        $eventId = $post->getInt('event_id');
        $event = $this->tryoutRepository->fetchById($eventId);
        if ($event === null)
        {
            throw new InvalidOrder('Evenement niet gevonden!');
        }

        $name = $post->getSimpleString('name');
        if (empty($name))
        {
            throw new InvalidOrder('Geen naam opgegeven!');
        }

        $email = $post->getEmail('email');
        if (empty($email))
        {
            throw new InvalidOrder('Geen e-mail opgegeven!');
        }

        $order = new Order();
        $order->tryout = $event;
        $order->name = $name;
        $order->email = $email;

        /** @var OrderTicketType[] $orderTicketTypes */
        $orderTicketTypes = [];
        $ticketTypes = $this->typeRepository->fetchAll();
        foreach ($ticketTypes as $ticketType)
        {
            assert($ticketType->id !== null);
            $amount = $post->getInt('tickettype-' . $ticketType->id);

            $ott = new OrderTicketType();
            $ott->order = $order;
            $ott->type = $ticketType;
            $ott->amount = $amount;

            $orderTicketTypes[] = $ott;
        }

        $orderTotal = $this->calculateOrderTotal($orderTicketTypes);
        if ($orderTotal <= 0)
        {
            throw new InvalidOrder('U heeft een bestelling van 0 kaarten geplaatst of het formulier is niet goed aangekomen.');
        }

        $this->orderRepository->save($order);

        foreach ($orderTicketTypes as $orderTicketType)
        {
            $this->ottRepository->save($orderTicketType);
        }

        $paymentLink = $this->getPaymentLink($order, $urlInfo->schemeAndHost);
        $this->sendOrderConfirmation($order, $paymentLink, $mailFactory);

        return $order;
    }

    private function getPaymentLink(Order $order, string $baseUrl): string
    {
        assert($order->id !== null);
        return "{$baseUrl}/tryout-ticket/pay/{$order->id}";
    }

    #[RouteAttribute('pay', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function pay(QueryBits $queryBits, Request $request, UserSession $userSession): Response
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
                'Deze order is al betaald! Check uw e-mail voor de betalingsbevestiging.'
            );
            return $this->pageRenderer->renderResponse($page);
        }
        if (!empty($order->transactionCode) && strtotime($order->modified->format('U')) > strtotime('-30 minutes'))
        {
            $page = new SimplePage('Betalen', 'Er loopt al een betaling voor deze order. Wacht 30 minuten om het opnieuw te proberen.');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_BAD_REQUEST);
        }

        $event = $order->tryout;
        $total = $this->calculateOrderTotal($this->ottRepository->fetchAllByOrder($order));

        $description = "Ticket(s) {$event->name}";
        $baseUrl = $request->getSchemeAndHttpHost();
        $webhookUrl = "{$baseUrl}/api/tryout-ticket/mollieWebhook";
        $redirectUrl = "{$baseUrl}/tryout-ticket/afterPayment/{$order->id}";

        $payment = new Payment($description, $total, Currency::EUR, $redirectUrl, $webhookUrl);
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
            $userSession->addNotification('Bedankt voor de bestelling! Helaas lukte het doorsturen naar de betaalpagina niet.');
            return new RedirectResponse('/');
        }

        $userSession->addNotification('Bedankt voor de betaling! Het kan even duren voordat deze geregistreerd is.');
        return new RedirectResponse($redirectUrl);
    }

    #[RouteAttribute('mollieWebhook', RequestMethod::POST, UserLevel::ANONYMOUS, isApiMethod: true, skipCSRFCheck: true)]
    public function mollieWebhook(RequestParameters $post, MailFactory $mailFactory): Response
    {
        $apiKey = $this->settingsRepository->get('mollieApiKey');
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
                    $this->setOrderAsPaidAndSendMail($order, $mailFactory);
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

    private function sendOrderConfirmation(Order $order, string $paymentLink, MailFactory $mailFactory): void
    {
        $this->orderRepository->save($order);
        $organisation = $this->settingsRepository->get(BuiltinSetting::ORGANISATION);

        $text = "Hartelijk dank voor uw bestelling bij {$organisation}.\n\n";
        $text .= "U kunt uw bestelling betalen middels de volgende link: {$paymentLink}\n\n";
        $text .= "Reserveringsnummer: {$order->id}\n\n";

        $orderTotal = OrderTotal::fromOrderTicketTypes($this->ottRepository->fetchAllByOrder($order));
        $text .= $orderTotal->asPlainText();

        $totalFormatted = ViewHelpers::formatEuro($orderTotal->total);
        $text .= "\nTotaalbedrag: {$totalFormatted}\n\n";
        $text .= "Met vriendelijke groet,\nSportschool Geelhoed";

        $mail = $mailFactory->createMailWithDefaults(
            new Address($order->email),
            'Bestelling',
            $text
        );
        $mail->send();
    }

    private function setOrderAsPaidAndSendMail(Order $order, MailFactory $mailFactory): void
    {
        $order->isPaid = true;
        $this->orderRepository->save($order);
        $organisation = $this->settingsRepository->get(BuiltinSetting::ORGANISATION);

        $text = "Hartelijk dank voor uw bestelling bij {$organisation}. Wij hebben uw betaling in goede orde ontvangen.\n\n";
        $text .= "Reserveringsnummer: {$order->id}\n\n";

        $orderTotal = OrderTotal::fromOrderTicketTypes($this->ottRepository->fetchAllByOrder($order));
        $text .= $orderTotal->asPlainText();

        $totalFormatted = ViewHelpers::formatEuro($orderTotal->total);
        $text .= "\nTotaalbedrag: {$totalFormatted}\n\n";
        $text .= "Met vriendelijke groet,\nSportschool Geelhoed";

        $mail = $mailFactory->createMailWithDefaults(
            new Address($order->email),
            'Betalingsbevestiging',
            $text
        );
        $mail->send();
    }

    /**
     * @param OrderTicketType[] $orderTicketTypes
     * @return float
     */
    public function calculateOrderTotal(array $orderTicketTypes): float
    {
        $total = 0.0;
        foreach ($orderTicketTypes as $orderTicketType)
        {
            $total += ($orderTicketType->amount * $orderTicketType->type->price);
        }

        return $total;
    }

    #[RouteAttribute('afterPayment', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function afterPayment(QueryBits $queryBits, OrderTicketTypeRepository $ottRepository): Response
    {
        $orderId = $queryBits->getInt(2);
        $order = $this->orderRepository->fetchById($orderId);
        if ($order !== null && $order->isPaid)
        {
            $orderTotal = OrderTotal::fromOrderTicketTypes($ottRepository->fetchAllByOrder($order));
            $page = new Page();
            $page->title = 'Bestelling verwerkt';
            $page->template = 'Geelhoed/Tryout/Ticket/PaymentComplete';
            $page->addTemplateVars([
                'order' => $order,
                'orderTotal' => $orderTotal,
            ]);
        }
        else
        {
            $page = new SimplePage(
                'Bestelling verwerkt',
                'Hartelijk dank voor uw bestelling. Als de betaling is gelukt, ontvangt binnen enkele minuten een e-mail met een betaalbevestiging.',
            );
        }

        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('getInfo', RequestMethod::GET, UserLevel::ANONYMOUS, isApiMethod: true)]
    public function getInfo(QueryBits $queryBits): JsonResponse
    {
        $eventId = $queryBits->getInt(2);
        if ($eventId < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $event = $this->tryoutRepository->fetchById($eventId);
        if ($event === null)
        {
            return new JsonResponse(['error' => 'Event does not exist!'], Response::HTTP_NOT_FOUND);
        }

        $ticketTypes = $this->typeRepository->fetchAll();

        $answer = [
            'tickettypes' => [],
        ];

        foreach ($ticketTypes as $ticketType)
        {
            $answer['tickettypes'][] = [
                'id' => $ticketType->id,
                'price' => $ticketType->price,
            ];
        }

        return new JsonResponse($answer);
    }

    private function getRequestedOrCurrentTryout(int $eventId): Tryout
    {
        $event = $this->tryoutRepository->fetchById($eventId);
        if ($event !== null)
        {
            return $event;
        }

        $now = new DateTime();
        $cutoff = new DateTime();
        $cutoff->add(new DateInterval('P1W'));
        $event = $this->tryoutRepository->fetch(
            ['start <= ?', 'end >= ?'],
            [$cutoff->format(Util::SQL_DATE_TIME_FORMAT), $now->format(Util::SQL_DATE_TIME_FORMAT)],
            'ORDER BY start'
        );
        if ($event == null)
        {
            throw new RuntimeUserSafeError('Geen actief tryout-evenement gevonden!');
        }

        return $event;
    }
}
