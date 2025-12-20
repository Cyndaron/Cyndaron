<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop;

use Cyndaron\Error\ErrorPage;
use Cyndaron\Geelhoed\Clubactie\Subscriber;
use Cyndaron\Geelhoed\Clubactie\SubscriberRepository;
use Cyndaron\Geelhoed\Hour\Hour;
use Cyndaron\Geelhoed\Hour\HourRepository;
use Cyndaron\Geelhoed\Location\LocationRepository;
use Cyndaron\Geelhoed\Webshop\Model\Currency;
use Cyndaron\Geelhoed\Webshop\Model\Order;
use Cyndaron\Geelhoed\Webshop\Model\OrderItem;
use Cyndaron\Geelhoed\Webshop\Model\OrderItemRepository;
use Cyndaron\Geelhoed\Webshop\Model\OrderRepository;
use Cyndaron\Geelhoed\Webshop\Model\OrderStatus;
use Cyndaron\Geelhoed\Webshop\Model\Product;
use Cyndaron\Geelhoed\Webshop\Model\ProductRepository;
use Cyndaron\Geelhoed\Webshop\Page\CreateAccountPage;
use Cyndaron\Geelhoed\Webshop\Page\FinishOrderPage;
use Cyndaron\Geelhoed\Webshop\Page\ItemTotalsPage;
use Cyndaron\Geelhoed\Webshop\Page\ManageOrderDetails;
use Cyndaron\Geelhoed\Webshop\Page\OverviewPage;
use Cyndaron\Geelhoed\Webshop\Page\ShopPage;
use Cyndaron\Page\Page;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Request\UrlInfo;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Spreadsheet\Helper as SpreadsheetHelper;
use Cyndaron\User\UserLevel;
use Cyndaron\User\UserSession;
use Cyndaron\Util\MailFactory;
use Cyndaron\Util\RuntimeUserSafeError;
use Cyndaron\Util\Setting;
use Cyndaron\Util\SettingsRepository;
use Cyndaron\Util\Util;
use Cyndaron\View\Template\ViewHelpers;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use function assert;
use function json_encode;
use function str_contains;
use function str_replace;
use function array_key_exists;
use function count;
use function implode;
use function str_increment;
use function ceil;
use function array_slice;
use function usort;
use function strtolower;

final class WebshopController
{
    public const RIGHT_MANAGE = 'orders_edit';

    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly OrderRepository $orderRepository,
        private readonly OrderItemRepository $orderItemRepository,
        private readonly SubscriberRepository $subscriberRepository,
        private readonly ProductRepository $productRepository,
    ) {
    }

    #[RouteAttribute('winkelen', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function shopPage(QueryBits $queryBits): Response
    {
        $hash = $queryBits->getString(2);
        $subscriber = $this->subscriberRepository->fetchByHash($hash);
        if ($subscriber === null)
        {
            return $this->pageRenderer->renderErrorResponse(
                new ErrorPage('Fout', 'Gebruiker niet gevonden')
            );
        }

        $order = $this->orderRepository->fetchBySubscriber($subscriber);
        if ($order === null)
        {
            $order = new Order();
            $order->subscriber = $subscriber;
            $order->hour = new Hour(1);
            $order->status = OrderStatus::QUOTE;
            $this->orderRepository->save($order);
        }

        if ($order->status !== OrderStatus::QUOTE)
        {
            return new RedirectResponse("/webwinkel/status/{$hash}");
        }

        $page = new ErrorPage(
            'Bestellen niet meer mogelijk',
            'De deadline voor bestellen is gesloten. We beginnen binnenkort met het verwerken van de bestellingen.',
            Response::HTTP_GONE
        );
        return $this->pageRenderer->renderErrorResponse($page);
        //$page = new ShopPage($subscriber, $order, $this->orderRepository, $this->orderItemRepository, $this->productRepository);
        //return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('overzicht', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function finishOrder(QueryBits $queryBits, LocationRepository $locationRepository): Response
    {
        $hash = $queryBits->getString(2);
        try
        {
            [$order, $subscriber] = $this->getSubscriberAndOrderFromHash($hash);
        }
        catch (RuntimeUserSafeError $e)
        {
            return $this->pageRenderer->renderErrorResponse(
                new ErrorPage('Fout', $e->getMessage())
            );
        }

        $page = new FinishOrderPage($subscriber, $order, $locationRepository, $this->orderRepository, $this->orderItemRepository);
        return $this->pageRenderer->renderResponse($page);
    }

    private function sendOrderConfirmationMail(UrlInfo $urlInfo, Subscriber $subscriber, Order $order, MailFactory $mailFactory): void
    {
        $text = "Beste {$subscriber->getFullName()},

We hebben je bestelling ontvangen.

Betalen kan met deze link: " . $urlInfo->schemeAndHost . '/webwinkel/bestelling-betalen/' . $subscriber->hash . "

Hieronder volgt een overzicht van de bestelde artikelen:
";
        $orderItems = $this->orderItemRepository->fetchAllByOrder($order);
        foreach ($orderItems as $orderItem)
        {
            $product = $orderItem->product;

            $text .= $orderItem->quantity . '× ';
            $text .= $product->name . ', ';
            foreach ($orderItem->getOptions() as $option)
            {
                $text .= $option . ', ';
            }
            if ($orderItem->currency === Currency::LOTTERY_TICKET)
            {
                $text .= "{$orderItem->getLineAmount()} loten";
            }
            else
            {
                $text .= ViewHelpers::formatEuro($orderItem->getLineAmount());
            }
            $text .= "\n";
        }

        $text .= "
Met vriendelijke groet,
Sportschool Geelhoed";


        $mail = $mailFactory->createMailWithDefaults(
            new Address($subscriber->email),
            'Bestelling webshop',
            $text
        );
        $mail->addReplyTo(new Address('gcageelhoed@gmail.com'));
        $mail->send();
    }

    public function sendAccountConfirmationMail(UrlInfo $urlInfo, Subscriber $subscriber, MailFactory $mailFactory): void
    {
        $link = "{$urlInfo->schemeAndHost}/webwinkel/winkelen/{$subscriber->hash}";
        $text = "Beste {$subscriber->getFullName()},

Je kunt vanaf nu bestellen.
";
        if ($subscriber->numSoldTickets > 0)
        {
            $text .= "\nAantal verkochte loten: {$subscriber->numSoldTickets}\n";
        }

        $text .= "
Je kunt bestellen met de volgende link: {$link}

Met vriendelijke groet,
Sportschool Geelhoed";


        $mail = $mailFactory->createMailWithDefaults(
            new Address($subscriber->email),
            'Bestellen voor Grote Clubactie',
            $text
        );
        $mail->addReplyTo(new Address('gcageelhoed@gmail.com'));
        $mail->send();
    }

    #[RouteAttribute('bestelling-plaatsen', RequestMethod::POST, UserLevel::ANONYMOUS, skipCSRFCheck: true)]
    public function placeOrder(RequestParameters $post, UrlInfo $urlInfo, MailFactory $mailFactory, HourRepository $hourRepository): Response
    {
        $hash = $post->getSimpleString('hash');
        try
        {
            [$order, $subscriber] = $this->getSubscriberAndOrderFromHash($hash);
        }
        catch (RuntimeUserSafeError $e)
        {
            return $this->pageRenderer->renderErrorResponse(
                new ErrorPage('Fout', $e->getMessage())
            );
        }

        $hour = $hourRepository->fetchById($post->getInt('hourId'));
        assert($hour !== null);
        $order->hour = $hour;
        $subscriber->phone = $post->getPhone('phone');
        $this->subscriberRepository->save($subscriber);
        $newStatus = $this->orderRepository->confirmByUser($order);
        $this->orderRepository->save($order);

        $this->sendOrderConfirmationMail($urlInfo, $subscriber, $order, $mailFactory);

        if ($newStatus === OrderStatus::PENDING_PAYMENT)
        {
            return new RedirectResponse("/webwinkel/bestelling-betalen/{$hash}");
        }

        return new RedirectResponse("/webwinkel/status/{$hash}");
    }

    #[RouteAttribute('status', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function status(QueryBits $queryBits): Response
    {
        $hash = $queryBits->getString(2);
        try
        {
            [$order] = $this->getSubscriberAndOrderFromHash($hash);
        }
        catch (RuntimeUserSafeError $e)
        {
            return $this->pageRenderer->renderErrorResponse(
                new ErrorPage('Fout', $e->getMessage())
            );
        }

        $status = match($order->status)
        {
            OrderStatus::QUOTE =>
                'De bestelling is nog niet door jou bevestigd.<br><a class="btn btn-primary" href="/webwinkel/winkelen/' . $hash . '">Verder winkelen</a>',
            OrderStatus::PENDING_TICKET_CHECK =>
                'De bestelling is geplaatst en wacht op controle van het lotenaantal.',
            OrderStatus::PENDING_PAYMENT =>
                'De bestelling is geplaatst en wacht op betaling.<br><a class="btn btn-primary" href="/webwinkel/bestelling-betalen/' . $hash . '">Betalen</a>',
            OrderStatus::IN_PROGRESS =>
                'De bestelling is in behandeling.',
            OrderStatus::SHIPPED_PARTIALLY =>
                'De bestelling is gedeeltelijk meegegeven aan de docent.',
            OrderStatus::SHIPPED_FULLY =>
                'De volledige bestelling is meegegeven aan de docent.',
        };

        $page = new SimplePage('Status bestelling', $status);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('bestelling-betalen', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function pay(QueryBits $queryBits, UrlInfo $urlInfo, UserSession $userSession): Response
    {
        $hash = $queryBits->getString(2);
        try
        {
            [$order, $subscriber] = $this->getSubscriberAndOrderFromHash($hash);
        }
        catch (RuntimeUserSafeError $e)
        {
            return $this->pageRenderer->renderErrorResponse(
                new ErrorPage('Fout', $e->getMessage())
            );
        }

        if ($order->status !== OrderStatus::PENDING_PAYMENT)
        {
            return new RedirectResponse("/webwinkel/status/{$hash}");
        }

        $paymentDescription = "Grote Clubactie 2024 {$subscriber->getFullName()}";
        $price = $this->orderRepository->getEuroSubtotal($order);
        $redirectUrl = "{$urlInfo->schemeAndHost}/webwinkel/status/{$hash}";
        $webhookUrl = "{$urlInfo->schemeAndHost}/api/webwinkel/mollieWebhook";
        $payment = new \Cyndaron\Payment\Payment(
            $paymentDescription,
            $price,
            \Cyndaron\Payment\Currency::EUR,
            $redirectUrl,
            $webhookUrl
        );
        $molliePayment = $payment->sendToMollie();

        if (empty($molliePayment->id))
        {
            $page = new SimplePage('Fout bij inschrijven', 'Betaling niet gevonden!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_NOT_FOUND);
        }

        $order->paymentId = $molliePayment->id;
        $this->orderRepository->save($order);

        $redirectUrl = $molliePayment->getCheckoutUrl();
        if ($redirectUrl === null)
        {
            $userSession->addNotification('Bedankt voor je inschrijving! Helaas lukte het doorsturen naar de betaalpagina niet.');
            return new RedirectResponse('/');
        }

        $userSession->addNotification('Bedankt voor de betaling! Het kan even duren voordat deze geregistreerd is.');
        return new RedirectResponse($redirectUrl);
    }

    #[RouteAttribute('mollieWebhook', RequestMethod::POST, UserLevel::ANONYMOUS, isApiMethod: true, skipCSRFCheck: true)]
    public function mollieWebhook(RequestParameters $post, MailFactory $mailFactory, SettingsRepository $settingsRepository): Response
    {
        $apiKey = $settingsRepository->get('mollieApiKey');
        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($apiKey);

        $id = $post->getUnfilteredString('id');
        $payment = $mollie->payments->get($id);
        $order = $this->orderRepository->fetch(['paymentId = ?'], [$id]);

        if ($order === null)
        {
            return new JsonResponse([]);
        }

        $paidStatus = false;
        if ($payment->isPaid() && !$payment->hasRefunds() && !$payment->hasChargebacks())
        {
            $paidStatus = true;
        }

        if ($paidStatus)
        {
            if ($order->status === OrderStatus::PENDING_PAYMENT)
            {
                $order->status = OrderStatus::IN_PROGRESS;
                $this->orderRepository->save($order);

                $subscriber = $order->subscriber;
                $text = "Beste {$subscriber->getFullName()},\n\nWe hebben de betaling voor je bestelling in onze webwinkel ontvangen.\n\n";
                $text .= "Met vriendelijke groet,\nSportschool Geelhoed";
                $mail = $mailFactory->createMailWithDefaults(
                    new Address($subscriber->email),
                    'Betaling gelukt',
                    $text
                );
                $mail->addReplyTo(new Address('gcageelhoed@gmail.com'));
                $mail->send();
            }
        }
        else
        {
            $order->status = OrderStatus::PENDING_PAYMENT;
            $this->orderRepository->save($order);
        }

        return new JsonResponse();
    }

    #[RouteAttribute('add-to-cart', RequestMethod::POST, UserLevel::ANONYMOUS, isApiMethod: true, skipCSRFCheck: true)]
    public function addToCart(RequestParameters $post): JsonResponse
    {
        $hash = $post->getSimpleString('hash');
        try
        {
            [$order] = $this->getSubscriberAndOrderFromHash($hash);
        }
        catch (RuntimeUserSafeError $e)
        {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $productId = $post->getInt('productId');
        $product = $this->productRepository->fetchById($productId);
        if ($product === null)
        {
            return new JsonResponse(['error' => 'Product niet gevonden'], Response::HTTP_BAD_REQUEST);
        }

        $options = $post->getSimpleString('options');
        $currency = Currency::from($post->getSimpleString('currency'));
        $price = $currency === Currency::LOTTERY_TICKET ? $product->getGcaTicketPrice() : $product->getEuroPrice();

        $newOrderItem = new OrderItem();
        $newOrderItem->order = $order;
        $newOrderItem->product = $product;
        $newOrderItem->options = $options;
        $newOrderItem->quantity = 1;
        $newOrderItem->currency = $currency;
        $newOrderItem->price = $price;

        foreach ($this->orderItemRepository->fetchAllByOrder($order) as $currentOrderItem)
        {
            if ($currentOrderItem->equals($newOrderItem))
            {
                $currentOrderItem->quantity += 1;
                $newOrderItem = $currentOrderItem;
                break;
            }
        }

        $this->orderItemRepository->save($newOrderItem);

        return new JsonResponse([]);
    }

    #[RouteAttribute('remove-from-cart', RequestMethod::POST, UserLevel::ANONYMOUS, isApiMethod: true, skipCSRFCheck: true)]
    public function removeFromCart(RequestParameters $post): JsonResponse
    {
        $hash = $post->getSimpleString('hash');
        try
        {
            [$order] = $this->getSubscriberAndOrderFromHash($hash);
        }
        catch (RuntimeUserSafeError $e)
        {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        if ($order->status !== OrderStatus::QUOTE)
        {
            return new JsonResponse(['error' => 'De order is al definitief!'], Response::HTTP_BAD_REQUEST);
        }

        $orderItemId = $post->getInt('orderItemId');
        $orderItem = $this->orderItemRepository->fetchById($orderItemId);
        if ($orderItem === null)
        {
            return new JsonResponse(['error' => 'Orderregel niet gevonden'], Response::HTTP_BAD_REQUEST);
        }

        if ($orderItem->order->id !== $order->id)
        {
            return new JsonResponse(['error' => 'Deze order is niet van jou!'], Response::HTTP_BAD_REQUEST);
        }

        $this->orderItemRepository->delete($orderItem);

        return new JsonResponse([]);
    }

    #[RouteAttribute('doneer-loten', RequestMethod::POST, UserLevel::ANONYMOUS, skipCSRFCheck: true)]
    public function donateRemainingTickets(QueryBits $queryBits): Response
    {
        $hash = $queryBits->getString(2);
        try
        {
            [$order, $subscriber] = $this->getSubscriberAndOrderFromHash($hash);
        }
        catch (RuntimeUserSafeError $e)
        {
            return $this->pageRenderer->renderErrorResponse(
                new ErrorPage('Fout', $e->getMessage())
            );
        }

        $donateProduct = $this->productRepository->fetchById(Product::DONATE_TICKETS_ID);
        if ($donateProduct === null)
        {
            return new RedirectResponse("/webwinkel/winkelen/{$hash}");
        }

        $numRemainingTickets = $subscriber->numSoldTickets - $this->orderRepository->getTicketTotal($order);
        if ($numRemainingTickets === 0)
        {
            return new RedirectResponse("/webwinkel/winkelen/{$hash}");
        }

        $orderItem = new OrderItem();
        $orderItem->order = $order;
        $orderItem->quantity = 1;
        $orderItem->product = $donateProduct;
        $orderItem->price = $numRemainingTickets;
        $orderItem->currency = Currency::LOTTERY_TICKET;
        $this->orderItemRepository->save($orderItem);

        return new RedirectResponse("/webwinkel/winkelen/{$hash}");
    }

    #[RouteAttribute('geen-gymtas', RequestMethod::POST, UserLevel::ANONYMOUS, skipCSRFCheck: true)]
    public function forfeitGymtas(QueryBits $queryBits): Response
    {
        $hash = $queryBits->getString(2);
        try
        {
            [$order, $subscriber] = $this->getSubscriberAndOrderFromHash($hash);
        }
        catch (RuntimeUserSafeError $e)
        {
            return $this->pageRenderer->renderErrorResponse(
                new ErrorPage('Fout', $e->getMessage())
            );
        }

        $gymtasProduct = $this->productRepository->fetchById(Product::GYMTAS_ID);
        if ($gymtasProduct === null)
        {
            return new RedirectResponse("/webwinkel/winkelen/{$hash}");
        }

        $numRemainingTickets = $subscriber->numSoldTickets - $this->orderRepository->getTicketTotal($order);
        if ($numRemainingTickets === 0)
        {
            return new RedirectResponse("/webwinkel/winkelen/{$hash}");
        }

        $orderItem = new OrderItem();
        $orderItem->order = $order;
        $orderItem->quantity = 1;
        $orderItem->product = $gymtasProduct;
        $orderItem->price = (float)$gymtasProduct->gcaTicketPrice;
        $orderItem->currency = Currency::LOTTERY_TICKET;
        $orderItem->options = json_encode(['color' => 'Achterwege laten'], flags: JSON_THROW_ON_ERROR);
        $this->orderItemRepository->save($orderItem);

        return new RedirectResponse("/webwinkel/winkelen/{$hash}");
    }

    /**
     * @param string $hash
     * @return array{0: Order, 1: Subscriber}
     */
    private function getSubscriberAndOrderFromHash(string $hash): array
    {
        $subscriber = $this->subscriberRepository->fetchByHash($hash);
        if ($subscriber === null)
        {
            throw new RuntimeUserSafeError('Gebruiker niet gevonden!');
        }

        $order = $this->orderRepository->fetchBySubscriber($subscriber);
        if ($order === null)
        {
            throw new RuntimeUserSafeError('Bestelling niet gevonden!');
        }

        return [$order, $subscriber];
    }

    #[RouteAttribute('account-aanmaken', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function createAccountGet(Request $request): Response
    {
        $skipTicketCheck = ($request->query->getAlpha('reden') === 'geenloten');
        $page = new CreateAccountPage($skipTicketCheck);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('account-aanmaken', RequestMethod::POST, UserLevel::ANONYMOUS, skipCSRFCheck: true)]
    public function createAccountPost(RequestParameters $post): Response
    {
        $firstName = $post->getSimpleString('firstName');
        $tussenvoegsel = $post->getSimpleString('tussenvoegsel');
        $lastName = $post->getSimpleString('lastName');
        $email = $post->getEmail('email');
        $skipTicketCheck = $post->getBool('skipTicketCheck');
        $hash = Util::generateToken(16);

        $subscriber = new Subscriber();
        $subscriber->firstName = $firstName;
        $subscriber->tussenvoegsel = $tussenvoegsel;
        $subscriber->lastName = $lastName;
        $subscriber->email = $email;
        $subscriber->numSoldTickets = 0;
        $subscriber->soldTicketsAreVerified = $skipTicketCheck;
        $subscriber->hash = $hash;
        $this->subscriberRepository->save($subscriber);

        if ($skipTicketCheck)
        {
            return new RedirectResponse("/webwinkel/winkelen/{$hash}");
        }

        $page = new SimplePage(
            'Aanvraag gelukt',
            'Je aanvraag is gelukt. Je krijgt automatisch bericht zodra we je lotenaantal hebben gecheckt.'
        );
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('send-mail', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: false, right: self::RIGHT_MANAGE, skipCSRFCheck: true)]
    public function sendMail(QueryBits $queryBits, UrlInfo $urlInfo, MailFactory $mailFactory): JsonResponse
    {
        $hash = $queryBits->getString(2);
        $subscriber = $this->subscriberRepository->fetchByHash($hash);
        if ($subscriber === null)
        {
            throw new RuntimeUserSafeError('Gebruiker niet gevonden!');
        }

        $this->sendAccountConfirmationMail($urlInfo, $subscriber, $mailFactory);
        $subscriber->emailSent = true;
        $this->subscriberRepository->save($subscriber);
        return new JsonResponse(['status' => 'ok']);
    }

    #[RouteAttribute('', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function overview(): Response
    {
        $page = new OverviewPage();
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('mail-everyone', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: self::RIGHT_MANAGE)]
    public function mailEveryone(UrlInfo $urlInfo, MailFactory $mailFactory): JsonResponse
    {
        $subscribers = $this->subscriberRepository->fetchAll(['soldTicketsAreVerified = 1', 'emailSent = 0']);
        foreach ($subscribers as $subscriber)
        {
            $this->sendAccountConfirmationMail($urlInfo, $subscriber, $mailFactory);
            $subscriber->emailSent = true;
            $this->subscriberRepository->save($subscriber);
        }

        return new JsonResponse(['status' => 'ok']);
    }

    #[RouteAttribute('bestellijst', RequestMethod::GET, UserLevel::ADMIN, right: self::RIGHT_MANAGE)]
    public function itemTotals(): Response
    {
        $page = new ItemTotalsPage($this->productRepository, $this->orderItemRepository);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('beheer-order', RequestMethod::GET, UserLevel::ADMIN, right: self::RIGHT_MANAGE)]
    public function manageOrderDetails(QueryBits $queryBits, OrderRepository $orderRepository, ManageOrderDetails $manageOrderDetails): Response
    {
        $orderId = $queryBits->getInt(2);
        $order = $orderRepository->fetchById($orderId);
        if ($order === null)
        {
            return $this->pageRenderer->renderErrorResponse(new ErrorPage('Fout', 'Order niet gevonden!', Response::HTTP_NOT_FOUND));
        }

        return $this->pageRenderer->renderResponse($manageOrderDetails->createPage($order));
    }

    #[RouteAttribute('uitleveren', RequestMethod::GET, UserLevel::ADMIN, right: self::RIGHT_MANAGE)]
    public function orderPickProductsGet(ProductRepository $productRepository): Response
    {
        $page = new Page();
        $page->title = 'Uitleveren - productkeuze';
        $page->template = 'Geelhoed/Webshop/Page/OrderPickProductSelectionPage';
        return $this->pageRenderer->renderResponse($page, ['products' => $productRepository->fetchAll()]);
    }

    #[RouteAttribute('uitleveren', RequestMethod::POST, UserLevel::ADMIN, right: self::RIGHT_MANAGE)]
    public function orderPickProductsPost(RequestParameters $post, OrderItemRepository $orderItemRepository): Response
    {
        /** @var int[] $productIds */
        $productIds = [];
        foreach ($post->getKeys() as $key)
        {
            if (str_contains($key, 'product-') && $post->getBool($key))
            {
                $id = (int)str_replace('product-', '', $key);
                $productIds[] = $id;
            }
        }

        if (empty($productIds))
        {
            $this->pageRenderer->renderErrorResponse(new ErrorPage('Uitleveren', 'Geen producten geselecteerd!', Response::HTTP_BAD_REQUEST));
        }

        $orderItems = $orderItemRepository->fetchAll(['productId IN (' . implode(',', $productIds) . ') AND orderId IN (SELECT id FROM geelhoed_webshop_order WHERE status=\'in_progress\')']);
        usort($orderItems, static function(OrderItem $oi1, OrderItem $oi2)
        {
            return strtolower($oi1->order->subscriber->lastName) <=> strtolower($oi2->order->subscriber->lastName);
        });

        /** @var array<int, list<OrderItem>> $byOrder */
        $byOrder = [];
        foreach ($orderItems as $orderItem)
        {
            $orderId = (int)$orderItem->order->id;
            if (!array_key_exists($orderId, $byOrder))
            {
                $byOrder[$orderId] = [];
            }

            $byOrder[$orderId][] = $orderItem;
        }

        $numOrders = count($byOrder);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $pageMargins = $sheet->getPageMargins();
        $pageMargins->setTop(0.0);
        $pageMargins->setBottom(0.0);
        $pageMargins->setLeft(0.0);
        $pageMargins->setRight(0.0);
        $endColumn = 'B';

        $style = $sheet->getStyle("A:{$endColumn}");
        $style->getFont()->setSize(12);
        $alignment = $style->getAlignment();
        $alignment->setVertical(Alignment::VERTICAL_TOP);
        $alignment->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $alignment->setWrapText(true);
        $alignment->setIndent(1);

        $column = 'A';
        while (true)
        {
            // Should be 105, but PHP Spreadsheet is off by a few mm.
            $width = 99;
            $sheet->getColumnDimension($column)->setWidth($width, 'mm');

            if ($column === $endColumn)
            {
                break;
            }

            $column = str_increment($column);
        }

        $row = 1;
        $column = 'A';
        foreach ($byOrder as $items)
        {
            $numItems = count($items);
            if ($numItems === 0)
            {
                continue;
            }

            $order = $items[0]->order;
            $subscriber = $order->subscriber;
            $hour = $order->hour;
            $les = "Les: " . ViewHelpers::getDutchWeekday($hour->day) . ", {$hour->location->street} {$hour->location->city}";

            $maxItemsPerSticker = 8;
            $numStickers = ceil($numItems / $maxItemsPerSticker);
            for ($currentSticker = 1; $currentSticker <= $numStickers; $currentSticker++)
            {
                $textLines = [''];
                $textLines[] = "Bestelling {$order->id}";
                $textLines[] = "{$subscriber->getFullName()}";
                $textLines[] = "{$subscriber->email} {$subscriber->phone}";
                $textLines[] = $les;
                $textLines[] = '';
                if ($numStickers > 1)
                {
                    $textLines[] = "Sticker {$currentSticker} van {$numStickers}";
                    $textLines[] = "";
                }

                $itemSlice = array_slice($items, ($currentSticker - 1) * $maxItemsPerSticker, $maxItemsPerSticker);
                foreach ($itemSlice as $orderItem)
                {
                    $textLines[] = "{$orderItem->quantity}× {$orderItem->getLineDescription()}";
                }

                $sheet->setCellValue("{$column}{$row}", implode("\n", $textLines));

                $sheet->getRowDimension($row)->setRowHeight(74, 'mm');
                if ($column === $endColumn)
                {
                    $column = 'A';
                    $row++;
                }
                else
                {
                    $column = str_increment($column);
                }
            }
        }

        $now = new \DateTimeImmutable();
        $httpHeaders = SpreadsheetHelper::getResponseHeadersForFilename('Uitlevering ' . $now->format('Y-m-d H:i:s'));
        return new Response(SpreadsheetHelper::convertToString($spreadsheet), Response::HTTP_OK, $httpHeaders);
    }
}
