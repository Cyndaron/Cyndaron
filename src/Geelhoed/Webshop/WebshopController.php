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

    private const OLD_HOURS_MAP = [
        1 => [1, 5],
        2 => [1, 5],
        3 => [1, 5],
        4 => [1, 5],
        5 => [1, 5],
        6 => [1, 5],
        7 => [2, 1],
        8 => [2, 1],
        9 => [2, 1],
        10 => [2, 1],
        11 => [2, 1],
        12 => [2, 1],
        13 => [3, 2],
        14 => [3, 2],
        15 => [3, 2],
        16 => [3, 2],
        17 => [4, 4],
        18 => [4, 4],
        19 => [4, 4],
        20 => [4, 4],
        21 => [4, 4],
        22 => [4, 5],
        23 => [4, 5],
        24 => [4, 5],
        25 => [4, 5],
        26 => [5, 5],
        27 => [5, 5],
        28 => [5, 5],
        29 => [5, 5],
        30 => [5, 5],
        31 => [7, 5],
        32 => [7, 5],
        33 => [7, 5],
        34 => [8, 2],
        35 => [8, 2],
        36 => [8, 2],
        37 => [8, 2],
        38 => [8, 2],
        40 => [9, 3],
        41 => [9, 3],
        42 => [9, 3],
        43 => [10, 4],
        44 => [10, 4],
        45 => [10, 4],
        46 => [10, 4],
        47 => [11, 5],
        48 => [11, 5],
        49 => [11, 5],
        50 => [11, 5],
        51 => [13, 6],
        52 => [13, 6],
        53 => [12, 2],
        54 => [12, 2],
        55 => [12, 2],
        56 => [12, 2],
        57 => [12, 2],
        58 => [12, 2],
        59 => [14, 3],
        60 => [14, 3],
        61 => [14, 3],
        62 => [14, 3],
        63 => [6, 1],
        64 => [6, 1],
        65 => [6, 1],
        66 => [6, 1],
        67 => [6, 1],
        68 => [6, 3],
        69 => [6, 3],
        70 => [6, 3],
        71 => [6, 6],
        72 => [6, 6],
        73 => [15, 1],
        74 => [15, 1],
        81 => [17, 4],
        82 => [17, 4],
        83 => [18, 5],
        84 => [18, 5],
        85 => [16, 2],
        86 => [16, 2],
        87 => [20, 5],
        88 => [20, 5],
        90 => [21, 4],
        91 => [21, 4],
        92 => [21, 4],
        93 => [22, 2],
        94 => [22, 2],
        97 => [9, 3],
        98 => [23, 3],
        99 => [23, 3],
        100 => [23, 3],
        101 => [23, 3],
        102 => [11, 5],
        103 => [11, 5],
        104 => [6, 3],
    ];

    public const ORDER_TO_HOUR_MAP = [
        11 => 1,
        12 => 4,
        13 => 56,
        14 => 5,
        16 => 4,
        17 => 69,
        18 => 15,
        19 => 14,
        20 => 55,
        21 => 27,
        22 => 90,
        23 => 102,
        24 => 65,
        25 => 50,
        26 => 65,
        27 => 48,
        28 => 64,
        29 => 100,
        30 => 9,
        31 => 3,
        32 => 3,
        33 => 2,
        34 => 3,
        35 => 66,
        36 => 18,
        37 => 1,
        38 => 1,
        39 => 14,
        40 => 3,
        41 => 28,
        42 => 3,
        43 => 14,
        44 => 26,
        45 => 48,
        46 => 53,
        47 => 100,
        48 => 51,
        49 => 14,
        50 => 23,
        51 => 22,
        52 => 51,
        53 => 62,
        54 => 69,
        55 => 2,
        56 => 17,
        57 => 50,
        58 => 87,
        59 => 7,
        60 => 19,
        61 => 3,
        62 => 27,
        63 => 36,
        64 => 55,
        65 => 55,
        66 => 72,
        67 => 63,
        68 => 28,
        69 => 27,
        70 => 46,
        71 => 49,
        72 => 56,
        73 => 99,
        74 => 1,
        75 => 71,
        76 => 2,
        77 => 8,
        78 => 91,
        79 => 100,
        80 => 99,
        81 => 47,
        82 => 3,
        83 => 8,
        84 => 14,
        85 => 60,
        86 => 99,
        87 => 51,
        88 => 1,
        89 => 14,
        90 => 51,
        91 => 1,
        92 => 59,
        93 => 1,
        94 => 100,
        95 => 1,
        96 => 27,
        97 => 63,
        98 => 61,
        99 => 3,
        100 => 51,
        101 => 51,
        102 => 15,
        103 => 65,
        104 => 46,
        105 => 46,
        106 => 27,
        107 => 100,
        108 => 9,
        109 => 51,
        110 => 65,
        111 => 72,
        112 => 59,
        113 => 1,
        114 => 45,
        115 => 1,
        116 => 61,
        117 => 99,
        118 => 1,
        119 => 99,
        120 => 3,
        121 => 44,
        122 => 1,
        123 => 10,
        124 => 61,
        125 => 14,
        126 => 59,
        127 => 6,
        128 => 18,
        129 => 64,
        130 => 32,
        131 => 20,
        132 => 19,
        133 => 56,
        134 => 27,
        135 => 99,
        136 => 51,
        137 => 65,
        138 => 40,
        139 => 19,
        140 => 60,
        141 => 72,
        142 => 18,
        143 => 63,
        144 => 34,
        145 => 100,
        146 => 51,
        147 => 1,
        148 => 18,
        149 => 55,
        150 => 43,
        151 => 1,
        152 => 72,
        153 => 71,
        154 => 97,
        155 => 40,
        156 => 26,
        157 => 11,
        158 => 69,
        159 => 70,
        160 => 57,
        161 => 87,
        162 => 22,
        163 => 36,
        164 => 57,
        165 => 103,
        166 => 61,
        167 => 2,
        168 => 27,
        169 => 72,
        170 => 64,
        171 => 28,
        172 => 56,
        173 => 1,
        174 => 15,
        175 => 1,
        176 => 54,
        177 => 17,
        178 => 49,
        179 => 20,
        180 => 47,
        181 => 36,
        182 => 13,
        183 => 27,
        184 => 69,
        185 => 1,
        186 => 5,
        187 => 4,
        188 => 4,
        189 => 64,
        190 => 18,
        191 => 51,
        192 => 19,
        193 => 56,
        194 => 35,
        195 => 48,
        196 => 55,
        197 => 46,
        198 => 99,
        199 => 50,
        200 => 44,
        201 => 17,
        202 => 19,
        203 => 17,
        204 => 102,
        205 => 50,
        206 => 54,
        207 => 50,
        208 => 1,
        209 => 44,
        210 => 19,
        211 => 49,
        212 => 4,
        213 => 20,
        214 => 64,
        215 => 2,
        216 => 50,
        217 => 18,
        218 => 14,
        219 => 53,
        220 => 3,
        221 => 19,
        222 => 49,
        223 => 9,
        224 => 52,
        225 => 56,
        226 => 49,
        227 => 53,
        228 => 88,
        229 => 1,
        230 => 1,
        231 => 47,
        232 => 13,
        233 => 97,
        234 => 55,
        235 => 59,
        236 => 97,
        237 => 29,
        238 => 28,
        239 => 54,
        240 => 44,
        241 => 65,
        242 => 8,
        243 => 61,
        244 => 15,
        245 => 49,
        246 => 44,
        247 => 3,
        248 => 102,
        249 => 1,
        250 => 46,
        251 => 4,
        252 => 44,
        253 => 2,
        254 => 66,
        255 => 55,
        256 => 3,
        257 => 88,
        258 => 87,
        259 => 46,
        260 => 23,
        261 => 19,
        262 => 14,
        263 => 64,
        264 => 27,
        265 => 27,
        266 => 14,
        267 => 71,
        268 => 34,
        269 => 98,
        270 => 4,
        271 => 3,
        272 => 3,
        273 => 90,
        274 => 4,
        275 => 48,
        276 => 4,
        277 => 1,
        278 => 1,
        279 => 1,
        280 => 1,
        281 => 19,
        282 => 48,
        283 => 69,
        284 => 70,
        285 => 97,
        286 => 2,
        287 => 3,
        288 => 4,
        289 => 99,
        290 => 4,
        291 => 44,
        292 => 49,
        293 => 34,
        294 => 51,
        295 => 24,
        296 => 5,
        297 => 99,
        298 => 45,
        299 => 18,
        300 => 55,
        301 => 49,
        302 => 51,
        303 => 65,
        304 => 65,
        305 => 4,
        306 => 100,
        307 => 49,
        308 => 14,
        309 => 5,
        310 => 3,
        311 => 97,
        312 => 44,
        313 => 61,
        314 => 1,
        315 => 14,
        316 => 5,
        317 => 54,
        318 => 62,
        319 => 36,
        320 => 19,
        321 => 62,
        322 => 51,
        323 => 1,
        324 => 1,
        325 => 99,
        326 => 100,
        327 => 24,
        328 => 99,
        329 => 56,
        330 => 57,
        331 => 2,
        332 => 1,
        333 => 48,
        334 => 64,
        335 => 40,
        336 => 2,
        337 => 87,
        338 => 35,
        339 => 14,
        340 => 5,
        341 => 1,
        342 => 44,
        343 => 71,
        344 => 19,
        345 => 62,
        346 => 57,
        347 => 24,
        348 => 99,
        349 => 1,
        350 => 1,
        351 => 72,
        352 => 1,
        353 => 49,
        354 => 1,
        355 => 27,
        356 => 13,
        357 => 1,
        358 => 64,
        359 => 94,
        360 => 29,
        361 => 55,
        362 => 9,
        363 => 1,
        364 => 71,
        365 => 47,
        366 => 1,
        367 => 1,
        368 => 3,
        369 => 53,
        370 => 1,
        371 => 4,
        372 => 13,
        373 => 45,
        374 => 45,
        375 => 1,
        376 => 102,
        377 => 1,
        378 => 61,
        379 => 66,
        380 => 3,
        381 => 9,
        382 => 55,
        383 => 2,
        384 => 1,
        385 => 44,
        386 => 20,
        387 => 48,
        388 => 44,
        389 => 1,
        390 => 49,
        391 => 41,
        392 => 1,
        393 => 14,
        394 => 5,
        395 => 54,
        396 => 2,
        397 => 100,
        398 => 19,
        399 => 54,
        400 => 55,
        401 => 1,
        402 => 1,
        403 => 100,
        404 => 44,
        405 => 1,
        406 => 45,
        407 => 1,
        408 => 1,
        409 => 100,
        410 => 99,
        411 => 100,
        412 => 15,
        413 => 1,
        414 => 1,
        415 => 28,
        416 => 1,
        417 => 35,
        418 => 46,
        419 => 100,
        420 => 7,
        421 => 17,
        422 => 47,
        423 => 101,
        424 => 71,
        425 => 1,
        426 => 35,
        427 => 1,
        428 => 45,
        429 => 1,
        430 => 55,
        431 => 1,
        432 => 46,
        433 => 50,
        434 => 1,
        435 => 51,
        436 => 2,
        437 => 14,
        438 => 1,
        439 => 51,
        440 => 99,
        441 => 19,
        442 => 64,
        443 => 18,
        444 => 1,
        445 => 61,
        446 => 1,
        447 => 48,
        448 => 1,
        449 => 1,
        450 => 50,
        451 => 43,
        452 => 2,
        453 => 97,
        454 => 65,
        455 => 29,
        456 => 1,
        457 => 1,
        458 => 1,
        459 => 1,
        460 => 49,
        461 => 45,
        462 => 49,
        463 => 3,
        464 => 49,
        465 => 1,
        466 => 27,
        467 => 8,
        468 => 1,
        469 => 61,
        470 => 1,
        471 => 40,
        472 => 1,
        473 => 53,
        474 => 1,
        475 => 61,
        476 => 92,
        477 => 3,
        478 => 1,
        479 => 1,
        480 => 1,
        481 => 1,
        482 => 20,
        483 => 1,
        484 => 2,
        485 => 99,
        486 => 1,
        487 => 1,
        488 => 36,
        489 => 27,
        490 => 1,
        491 => 1,
        492 => 1,
        493 => 102,
        494 => 1,
        495 => 1,
        496 => 43,
        497 => 1,
        498 => 1,
        499 => 1,
        500 => 1,
        501 => 1,
        502 => 18,
        503 => 1,
        504 => 66,
        505 => 14,
        506 => 14,
        507 => 19,
        508 => 4,
        509 => 54,
        510 => 1,
        511 => 1,
        512 => 1,
        513 => 1,
        514 => 4,
        515 => 1,
        516 => 1,
        517 => 1,
        518 => 31,
        519 => 66,
        520 => 28,
        521 => 1,
        522 => 14,
        523 => 1,
        524 => 1,
        525 => 66,
        526 => 1,
        527 => 42,
        528 => 97,
        529 => 40,
        530 => 1,
        531 => 26,
        532 => 1,
        533 => 65,
        534 => 1,
        535 => 56,
        536 => 48,
        537 => 28,
        538 => 1,
        539 => 1,
        540 => 40,
        541 => 1,
        542 => 1,
        543 => 1,
        544 => 3,
        545 => 1,
        546 => 1,
        547 => 1,
        548 => 1,
        549 => 102,
        550 => 1,
        551 => 17,
        552 => 1,
        553 => 56,
        554 => 3,
        555 => 1,
        556 => 56,
        557 => 1,
        558 => 69,
        559 => 27,
        560 => 1,
        561 => 3,
        562 => 1,
        563 => 4,
        564 => 1,
        565 => 70,
        566 => 66,
        567 => 1,
        568 => 1,
        569 => 9,
        570 => 1,
        571 => 1,
        572 => 1,
        573 => 1,
        574 => 1,
        575 => 1,
        576 => 1,
        577 => 1,
        578 => 1,
        579 => 44,
        580 => 43,
        581 => 1,
        582 => 1,
        583 => 1,
        584 => 1,
        585 => 50,
        586 => 1,
        587 => 1,
        588 => 1,
        589 => 60,
        590 => 1,
        591 => 1,
        592 => 1,
        593 => 1,
        594 => 1,
        595 => 1,
        596 => 6,
        597 => 1,
        598 => 54,
        599 => 51,
        600 => 45,
        601 => 1,
        602 => 3,
        603 => 1,
        604 => 1,
        605 => 1,
        606 => 1,
        607 => 61,
        608 => 18,
        609 => 1,
        610 => 1,
        611 => 19,
        612 => 1,
        613 => 1,
        614 => 1,
        615 => 20,
        616 => 52,
        617 => 52,
        618 => 27,
        619 => 28,
        620 => 1,
        621 => 1,
        622 => 1,
        623 => 1,
        624 => 1,
        625 => 1,
        626 => 1,
        627 => 1,
        628 => 1,
        629 => 1,
        630 => 1,
        631 => 3,
        632 => 1,
        633 => 1,
        634 => 1,
        635 => 66,
        636 => 1,
        637 => 62,
        638 => 65,
        639 => 1,
        640 => 1,
        641 => 1,
        642 => 1,
        643 => 1,
        644 => 1,
        645 => 1,
    ];

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

        $page = Page::createSimple('Status bestelling', $status);
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
            $page = Page::createSimple('Fout bij inschrijven', 'Betaling niet gevonden!');
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

        $page = Page::createSimple(
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
    public function orderPickProductsPost(RequestParameters $post, OrderItemRepository $orderItemRepository, ProductRepository $productRepository, LocationRepository $locationRepository): Response
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

        $gymtas = $productRepository->fetchById(1);
        assert($gymtas !== null);

        /** @var array<int, list<OrderItem>> $byOrder */
        $byOrder = [];
        foreach ($orderItems as $orderItem)
        {
            if (str_contains($orderItem->options, 'Achterwege laten'))
            {
                continue;
            }

            if ($orderItem->product->id === 19)
            {
                $orderItem->product = $gymtas;
            }

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
            assert($order->id !== null);
            $subscriber = $order->subscriber;
            $hour = $order->hour;
            $day = $hour->day;
            $location = $hour->location;
            $postfix = '(?)';
            if (array_key_exists($order->id, self::ORDER_TO_HOUR_MAP))
            {
                $oldHourId = self::ORDER_TO_HOUR_MAP[$order->id];
                if (array_key_exists($oldHourId, self::OLD_HOURS_MAP))
                {

                    $oldLocationId = self::OLD_HOURS_MAP[$oldHourId][0];
                    $oldLocation = $locationRepository->fetchById($oldLocationId);
                    if ($oldLocation !== null)
                    {
                        $location = $oldLocation;
                        $day = self::OLD_HOURS_MAP[$oldHourId][1];
                        $postfix = '';
                    }
                }
            }
            $les = "Les: " . ViewHelpers::getDutchWeekday($day) . ", {$location->street} {$location->city} {$postfix}";

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
