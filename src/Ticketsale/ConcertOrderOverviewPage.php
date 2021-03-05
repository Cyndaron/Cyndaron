<?php
namespace Cyndaron\Ticketsale;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Page;
use function array_key_exists;

final class ConcertOrderOverviewPage extends Page
{
    private const TICKET_TYPES_QUERY = 'SELECT * FROM `ticketsale_tickettypes` WHERE concertId=? ORDER BY price DESC';

    private const ORDERS_QUERY = 'SELECT DISTINCT b.id AS bestellingsnr,lastName,initials,`email`,street,houseNumber,houseNumberAddition,postcode,city,delivery,isDelivered,hasReservedSeats,isPaid,comments,deliveryByMember,deliveryMemberName,addressIsAbroad
                    FROM     `ticketsale_orders` AS b,
                            `ticketsale_orders_tickettypes` AS bk,
                            `ticketsale_tickettypes` AS k
                    WHERE b.id=bk.orderId AND k.id=bk.tickettypeId AND k.concertId=?
                    ORDER BY bestellingsnr;';

    private const BOUGHT_TICKET_TYPES_QUERY = 'SELECT orderId,tickettypeId,amount
                    FROM     `ticketsale_orders_tickettypes`';

    public function __construct(Concert $concert)
    {
        $ticketTypes = DBConnection::doQueryAndFetchAll(self::TICKET_TYPES_QUERY, [$concert->id]);
        $orders = DBConnection::doQueryAndFetchAll(self::ORDERS_QUERY, [$concert->id]);
        $ticketTypesByOrder = $this->getTicketTypesPerOrder($concert);
        parent::__construct('Overzicht bestellingen: ' . $concert->name);
        $this->addScript('/src/Ticketsale/ConcertOrderOverviewPage.js');
        $this->addTemplateVars([
            'ticketTypes' => $ticketTypes,
            'concert' => $concert,
            'orders' => $orders,
            'ticketTypesByOrder' => $ticketTypesByOrder,
        ]);
    }

    /**
     * @param Concert $concert
     * @return array
     */
    private function getTicketTypesPerOrder(Concert $concert): array
    {
        $boughtTicketTypes = DBConnection::doQueryAndFetchAll(self::BOUGHT_TICKET_TYPES_QUERY, [$concert->id]) ?: [];
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
