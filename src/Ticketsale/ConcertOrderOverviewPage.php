<?php
namespace Cyndaron\Ticketsale;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Ticketsale\Order\Order;
use Cyndaron\View\Page;
use function array_key_exists;

final class ConcertOrderOverviewPage extends Page
{
    protected string $extraBodyClasses = 'ticketsale-concert-order-overview';

    private const TICKET_TYPES_QUERY = 'SELECT * FROM `ticketsale_tickettypes` WHERE concertId=? ORDER BY price DESC';

    private const BOUGHT_TICKET_TYPES_QUERY = 'SELECT orderId, tickettypeId, SUM(amount) AS amount
        FROM `ticketsale_orders_tickettypes`
        GROUP BY orderId,tickettypeId;';

    public function __construct(Concert $concert)
    {
        $ticketTypes = DBConnection::doQueryAndFetchAll(self::TICKET_TYPES_QUERY, [$concert->id]);
        $ticketTypesByOrder = $this->getTicketTypesPerOrder($concert);
        $orders = Order::fetchByConcert($concert);
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

        parent::__construct('Overzicht bestellingen: ' . $concert->name);
        $this->addScript('/src/Ticketsale/js/ConcertOrderOverviewPage.js');
        $this->addCss('/src/Ticketsale/css/Ticketsale.min.css');
        $this->addTemplateVars([
            'ticketTypes' => $ticketTypes,
            'concert' => $concert,
            'orders' => $orders,
            'ticketTypesByOrder' => $ticketTypesByOrder,
            'totals' => $totals,
        ]);
    }

    /**
     * @param Concert $concert
     * @return array
     */
    private function getTicketTypesPerOrder(Concert $concert): array
    {
        $boughtTicketTypes = DBConnection::doQueryAndFetchAll(self::BOUGHT_TICKET_TYPES_QUERY) ?: [];
        $ticketTypesByOrder = [];
        foreach ($boughtTicketTypes as $boughtTicketType)
        {
            $orderId = $boughtTicketType['orderId'];
            $ticketTypeId = $boughtTicketType['tickettypeId'];
            if (!array_key_exists($orderId, $ticketTypesByOrder))
            {
                $ticketTypesByOrder[$orderId] = [];
            }

            $ticketTypesByOrder[$orderId][$ticketTypeId] = $boughtTicketType['amount'];
        }

        return $ticketTypesByOrder;
    }
}
