<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout\PageManager;

use Cyndaron\Geelhoed\Tryout\Ticket\OrderRepository;
use Cyndaron\Geelhoed\Tryout\Ticket\OrderTicketTypeRepository;
use Cyndaron\Geelhoed\Tryout\Ticket\OrderTotal;
use Cyndaron\Geelhoed\Tryout\TryoutRepository;
use Cyndaron\PageManager\PageManagerTabInterface;
use Cyndaron\Request\QueryBits;
use Cyndaron\Util\Util;
use Cyndaron\View\Template\TemplateRenderer;
use DateInterval;
use DateTime;
use function usort;

final class OrdersTab implements PageManagerTabInterface
{
    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
        private readonly TryoutRepository $tryoutRepository,
        private readonly OrderRepository $orderRepository,
        private readonly OrderTicketTypeRepository $ottRepository
    ) {
    }

    public function render(QueryBits $queryBits): string
    {
        $eventId = $queryBits->getInt(2);
        $event = $this->tryoutRepository->fetchById($eventId);
        if ($event === null)
        {
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
                return 'Geen actueel toernooi.';
            }
        }

        $orderRecords = [];
        foreach ($this->orderRepository->fetchByEvent($event) as $order)
        {
            if (!$order->isPaid)
            {
                continue;
            }

            $orderRecords[$order->id]['order'] = $order;
            $orderTicketTypes = $this->ottRepository->fetchAllByOrder($order);
            $orderRecords[$order->id]['orderTotal'] = OrderTotal::fromOrderTicketTypes($orderTicketTypes);
        }

        usort($orderRecords, function(array $input1, array $input2)
        {
            return $input1['order']->name <=> $input2['order']->name;
        });

        return $this->templateRenderer->render('Geelhoed/Tryout/Ticket/PageManagerTabOrders', [
            'event' => $event,
            'orderRecords' => $orderRecords,
        ]);
    }
}
