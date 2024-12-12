<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop;

use Cyndaron\Error\ErrorPage;
use Cyndaron\Geelhoed\Clubactie\Subscriber;
use Cyndaron\Geelhoed\Webshop\Model\Currency;
use Cyndaron\Geelhoed\Webshop\Model\Order;
use Cyndaron\Geelhoed\Webshop\Model\OrderItem;
use Cyndaron\Geelhoed\Webshop\Model\OrderStatus;
use Cyndaron\Geelhoed\Webshop\Model\Product;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Request\UrlInfo;
use Cyndaron\Routing\Controller;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Cyndaron\User\UserSession;
use Cyndaron\Util\Mail as UtilMail;
use Cyndaron\Util\RuntimeUserSafeError;
use Cyndaron\Util\Setting;
use Cyndaron\Util\Util;
use Cyndaron\View\Template\ViewHelpers;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use function sprintf;

final class WebshopController extends Controller
{
    public const RIGHT_MANAGE = 'orders_edit';

    #[RouteAttribute('winkelen', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function shopPage(QueryBits $queryBits): Response
    {
        $hash = $queryBits->getString(2);
        $subscriber = Subscriber::fetchByHash($hash);
        if ($subscriber === null)
        {
            return $this->pageRenderer->renderErrorResponse(
                new ErrorPage('Fout', 'Gebruiker niet gevonden')
            );
        }

        $order = Order::fetchBySubscriber($subscriber);
        if ($order === null)
        {
            $order = new Order();
            $order->subscriberId = (int)$subscriber->id;
            $order->status = OrderStatus::QUOTE;
            $order->save();
        }

        if ($order->status !== OrderStatus::QUOTE)
        {
            return new RedirectResponse("/webwinkel/status/{$hash}");
        }

        $page = new ShopPage($subscriber, $order);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('overzicht', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function finishOrder(QueryBits $queryBits): Response
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

        $page = new FinishOrderPage($subscriber, $order);
        return $this->pageRenderer->renderResponse($page);
    }

    private function sendOrderConfirmationMail(UrlInfo $urlInfo, Subscriber $subscriber, Order $order): void
    {
        $text = "Beste {$subscriber->getFullName()},

We hebben je bestelling ontvangen.
Hieronder volgt een overzicht van de bestelde artikelen:
";
        $orderItems = OrderItem::fetchAllByOrder($order);
        foreach ($orderItems as $orderItem)
        {
            $product = $orderItem->getProduct();

            $text = $orderItem->quantity . '× ';
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


        $mail = UtilMail::createMailWithDefaults(
            $urlInfo->domain,
            new Address($subscriber->email),
            'Bestelling webshop',
            $text
        );
        $mail->send();
    }

    private function sendAccountConfirmationMail(UrlInfo $urlInfo, Subscriber $subscriber): void
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


        $mail = UtilMail::createMailWithDefaults(
            $urlInfo->domain,
            new Address($subscriber->email),
            'Bestellen voor Grote Clubactie',
            $text
        );
        $mail->send();
    }

    #[RouteAttribute('bestelling-plaatsen', RequestMethod::POST, UserLevel::ANONYMOUS, skipCSRFCheck: true)]
    public function placeOrder(RequestParameters $post, UrlInfo $urlInfo): Response
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

        $order->hourId = $post->getInt('hourId');
        $subscriber->phone = $post->getPhone('phone');
        $subscriber->save();
        $newStatus = $order->confirmByUser();
        $order->save();

        $this->sendOrderConfirmationMail($urlInfo, $subscriber, $order);

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
        $price = $order->getEuroSubtotal();
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
        $order->save();

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
    public function mollieWebhook(RequestParameters $post, LoggerInterface $logger): Response
    {
        $apiKey = Setting::get('mollieApiKey');
        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($apiKey);

        $id = $post->getUnfilteredString('id');
        $payment = $mollie->payments->get($id);
        $order = Order::fetch(['paymentId = ?'], [$id]);

        if ($order === null)
        {
            $message = sprintf('Poging tot updaten van transactie met id %s mislukt.', $id);
            $message .= ' Order niet gevonden.';

            $logger->error($message);
            return new JsonResponse(['error' => 'Could not find payment!'], Response::HTTP_NOT_FOUND);
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
            }
        }
        else
        {
            $order->status = OrderStatus::PENDING_PAYMENT;
        }

        if (!$order->save())
        {
            return new JsonResponse(['error' => 'Could not update payment information for all subscriptions!'], Response::HTTP_INTERNAL_SERVER_ERROR);
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
        $product = Product::fetchById($productId);
        if ($product === null)
        {
            return new JsonResponse(['error' => 'Product niet gevonden'], Response::HTTP_BAD_REQUEST);
        }

        $options = $post->getSimpleString('options');
        $currency = Currency::from($post->getSimpleString('currency'));
        $price = $currency === Currency::LOTTERY_TICKET ? $product->getGcaTicketPrice() : $product->getEuroPrice();

        $newOrderItem = new OrderItem();
        $newOrderItem->orderId = (int)$order->id;
        $newOrderItem->productId = $productId;
        $newOrderItem->options = $options;
        $newOrderItem->quantity = 1;
        $newOrderItem->currency = $currency;
        $newOrderItem->price = $price;

        foreach (OrderItem::fetchAllByOrder($order) as $currentOrderItem)
        {
            if ($currentOrderItem->equals($newOrderItem))
            {
                $currentOrderItem->quantity += 1;
                $newOrderItem = $currentOrderItem;
                break;
            }
        }

        $newOrderItem->save();

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
        $orderItem = OrderItem::fetchById($orderItemId);
        if ($orderItem === null)
        {
            return new JsonResponse(['error' => 'Orderregel niet gevonden'], Response::HTTP_BAD_REQUEST);
        }

        if ($orderItem->orderId !== $order->id)
        {
            return new JsonResponse(['error' => 'Deze order is niet van jou!'], Response::HTTP_BAD_REQUEST);
        }

        $orderItem->delete();

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

        $donateProduct = Product::fetchById(Product::DONATE_TICKETS_ID);
        if ($donateProduct === null)
        {
            return new RedirectResponse("/webwinkel/winkelen/{$hash}");
        }

        $numRemainingTickets = $subscriber->numSoldTickets - $order->getTicketTotal();
        if ($numRemainingTickets === 0)
        {
            return new RedirectResponse("/webwinkel/winkelen/{$hash}");
        }

        $orderItem = new OrderItem();
        $orderItem->orderId = (int)$order->id;
        $orderItem->quantity = 1;
        $orderItem->productId = (int)$donateProduct->id;
        $orderItem->price = $numRemainingTickets;
        $orderItem->currency = Currency::LOTTERY_TICKET;
        $orderItem->save();

        return new RedirectResponse("/webwinkel/winkelen/{$hash}");
    }

    /**
     * @param string $hash
     * @return array{0: Order, 1: Subscriber}
     */
    private function getSubscriberAndOrderFromHash(string $hash): array
    {
        $subscriber = Subscriber::fetchByHash($hash);
        if ($subscriber === null)
        {
            throw new RuntimeUserSafeError('Gebruiker niet gevonden!');
        }

        $order = Order::fetchBySubscriber($subscriber);
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
        $subscriber->save();

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
    public function sendMail(QueryBits $queryBits, UrlInfo $urlInfo): JsonResponse
    {
        $hash = $queryBits->getString(2);
        $subscriber = Subscriber::fetchByHash($hash);
        if ($subscriber === null)
        {
            throw new RuntimeUserSafeError('Gebruiker niet gevonden!');
        }

        $this->sendAccountConfirmationMail($urlInfo, $subscriber);
        $subscriber->emailSent = true;
        $subscriber->save();
        return new JsonResponse(['status' => 'ok']);
    }

    #[RouteAttribute('', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function overview(): Response
    {
        $page = new OverviewPage();
        return $this->pageRenderer->renderResponse($page);
    }
}
