<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop;

use Cyndaron\Error\ErrorPage;
use Cyndaron\Geelhoed\Clubactie\Subscriber;
use Cyndaron\Geelhoed\Contest\ContestMember;
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
use Cyndaron\Util\Setting;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use function Safe\error_log;
use function sprintf;

final class WebshopController extends Controller
{
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

        $page = new ShopPage($subscriber, $order);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('overzicht', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function finishOrder(QueryBits $queryBits): Response
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
            return $this->pageRenderer->renderErrorResponse(
                new ErrorPage('Fout', 'Bestelling niet gevonden')
            );
        }

        $page = new FinishOrderPage($subscriber, $order);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('bestelling-plaatsen', RequestMethod::POST, UserLevel::ANONYMOUS, skipCSRFCheck: true)]
    public function placeOrder(RequestParameters $post): Response
    {
        $hash = $post->getSimpleString('hash');
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
            return $this->pageRenderer->renderErrorResponse(
                new ErrorPage('Fout', 'Bestelling niet gevonden')
            );
        }

        $order->hourId = $post->getInt('hourId');
        $subscriber->phone = $post->getPhone('phone');
        $subscriber->save();
        $newStatus = $order->confirmByUser();
        $order->save();

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
            return $this->pageRenderer->renderErrorResponse(
                new ErrorPage('Fout', 'Bestelling niet gevonden')
            );
        }

        $status = match($order->status)
        {
            OrderStatus::QUOTE =>
                'De bestelling is nog niet door jou bevestigd.<br><a class="btn btn-primary" href="/webwinkel/winkelen/' . $hash . '">Verder winkelen</a>',
            OrderStatus::PENDING_TICKET_CHECK =>
                'De bestelling is geplaatst en wacht op controle van het lotenaantal.',
            OrderStatus::PENDING_PAYMENT =>
                'De bestelling is geplaatst en wacht op betaling',
            OrderStatus::IN_PROGRESS =>
                'De bestelling is in behandeling.',
            OrderStatus::DELIVERED =>
                'De bestelling is bezorgd.',
        };

        $page = new SimplePage('Status bestelling', $status);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('bestelling-betalen', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function pay(QueryBits $queryBits, UrlInfo $urlInfo, UserSession $userSession): Response
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
            return $this->pageRenderer->renderErrorResponse(
                new ErrorPage('Fout', 'Bestelling niet gevonden')
            );
        }

        if ($order->status !== OrderStatus::PENDING_PAYMENT)
        {
            return new RedirectResponse("/webwinkel/status/{$hash}");
        }

        $webhookUrl = "{$urlInfo->schemeAndHost}/api/webwinkel/mollieWebhook";

        $paymentDescription = "Grote Clubactie 2024 {$subscriber->getFullName()}";
        $price = $order->getEuroSubtotal();
        $redirectUrl = "/webwinkel/status/{$hash}";
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
        $subscriber = Subscriber::fetchByHash($hash);
        if ($subscriber === null)
        {
            return new JsonResponse(['error' => 'Gebruiker niet gevonden'], Response::HTTP_BAD_REQUEST);
        }

        $order = Order::fetchBySubscriber($subscriber);
        if ($order === null)
        {
            return new JsonResponse(['error' => 'Bestelling niet gevonden'], Response::HTTP_BAD_REQUEST);
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

        $orderItem = new OrderItem();
        $orderItem->orderId = (int)$order->id;
        $orderItem->productId = $productId;
        $orderItem->options = $options;
        $orderItem->quantity = 1;
        $orderItem->currency = $currency;
        $orderItem->price = $price;
        $orderItem->save();

        return new JsonResponse([]);
    }

    #[RouteAttribute('remove-from-cart', RequestMethod::POST, UserLevel::ANONYMOUS, isApiMethod: true, skipCSRFCheck: true)]
    public function removeFromCart(RequestParameters $post): JsonResponse
    {
        $hash = $post->getSimpleString('hash');
        $subscriber = Subscriber::fetchByHash($hash);
        if ($subscriber === null)
        {
            return new JsonResponse(['error' => 'Gebruiker niet gevonden'], Response::HTTP_BAD_REQUEST);
        }

        $order = Order::fetchBySubscriber($subscriber);
        if ($order === null)
        {
            return new JsonResponse(['error' => 'Bestelling niet gevonden'], Response::HTTP_BAD_REQUEST);
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
}
