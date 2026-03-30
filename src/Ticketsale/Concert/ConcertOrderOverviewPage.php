<?php
namespace Cyndaron\Ticketsale\Concert;

use Cyndaron\DBAL\Connection;
use Cyndaron\Page\Page;
use Cyndaron\Ticketsale\Order\OrderHelper;
use Cyndaron\Ticketsale\Order\OrderRepository;
use Cyndaron\Ticketsale\TicketType\TicketTypeRepository;
use function array_key_exists;

final class ConcertOrderOverviewPage extends Page
{
    public string $extraBodyClasses = 'ticketsale-concert-order-overview';

    private const BOUGHT_TICKET_TYPES_QUERY = 'SELECT orderId, tickettypeId, SUM(amount) AS amount
        FROM `ticketsale_orders_tickettypes`
        GROUP BY orderId,tickettypeId;';

    public function __construct(Concert $concert, TicketTypeRepository $ticketTypeRepository, OrderRepository $orderRepository, Connection $connection, OrderHelper $orderHelper)
    {
        $ticketTypes = $ticketTypeRepository->fetchByConcertAndSortByPrice($concert);
        $ticketTypesByOrder = $this->getTicketTypesPerOrder($connection);
        $orders = $orderRepository->fetchByConcert($concert);
        $totals = [];
        foreach ($ticketTypesByOrder as $ticketTypesForOneOrder)
        {
            foreach ($ticketTypesForOneOrder as $ticketType => $amount)
            {
                if (!array_key_exists($ticketType, $totals))
                {
                    $totals[$ticketType] = 0;
                }
                $totals[$ticketType] += $amount;
            }
        }

        $this->title = 'Overzicht bestellingen: ' . $concert->name;
        $this->addScript('/src/Ticketsale/js/ConcertOrderOverviewPage.js');
        $this->addCss('/src/Ticketsale/css/Ticketsale.min.css');
        $this->addTemplateVars([
            'ticketTypes' => $ticketTypes,
            'concert' => $concert,
            'orders' => $orders,
            'ticketTypesByOrder' => $ticketTypesByOrder,
            'totals' => $totals,
            'orderHelper' => $orderHelper,
        ]);
    }

    /**
     * @return array<int, array<int, int>>
     */
    private function getTicketTypesPerOrder(Connection $connection): array
    {
        $boughtTicketTypes = $connection->doQueryAndFetchAll(self::BOUGHT_TICKET_TYPES_QUERY) ?: [];
        $ticketTypesByOrder = [];
        foreach ($boughtTicketTypes as $boughtTicketType)
        {
            $orderId = (int)$boughtTicketType['orderId'];
            $ticketTypeId = (int)$boughtTicketType['tickettypeId'];
            if (!array_key_exists($orderId, $ticketTypesByOrder))
            {
                $ticketTypesByOrder[$orderId] = [];
            }

            $ticketTypesByOrder[$orderId][$ticketTypeId] = (int)$boughtTicketType['amount'];
        }

        return $ticketTypesByOrder;
    }
}
