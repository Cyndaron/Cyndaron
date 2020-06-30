<?php
namespace Cyndaron\Ticketsale;

use Cyndaron\DBConnection;
use Cyndaron\Page;

class ConcertOrderOverviewPage extends Page
{
    public function __construct(Concert $concert)
    {
        $ticketTypesQuery = 'SELECT * FROM `ticketsale_tickettypes` WHERE concertId=? ORDER BY price DESC';
        $ticketTypes = DBConnection::doQueryAndFetchAll($ticketTypesQuery, [$concert->id]);

        $ordersQuery = 'SELECT DISTINCT b.id AS bestellingsnr,lastName,initials,`email`,street,houseNumber,houseNumberAddition,postcode,city,delivery,isDelivered,hasReservedSeats,isPaid,comments,deliveryByMember,deliveryMemberName,addressIsAbroad
                    FROM     `ticketsale_orders` AS b,
                            `ticketsale_orders_tickettypes` AS bk,
                            `ticketsale_tickettypes` AS k
                    WHERE b.id=bk.orderId AND k.id=bk.tickettypeId AND k.concertId=?
                    ORDER BY bestellingsnr;';
        $orders = DBConnection::doQueryAndFetchAll($ordersQuery, [$concert->id]);
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
        $boughtTicketTypesQuery = 'SELECT orderId,tickettypeId,amount
                    FROM     `ticketsale_orders_tickettypes`';
        $boughtTicketTypes = DBConnection::doQueryAndFetchAll($boughtTicketTypesQuery, [$concert->id]) ?: [];

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
