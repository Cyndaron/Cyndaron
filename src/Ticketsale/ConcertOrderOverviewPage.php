<?php
namespace Cyndaron\Ticketsale;

use Cyndaron\DBConnection;
use Cyndaron\Page;

class ConcertOrderOverviewPage extends Page
{
    public function __construct(int $concertId)
    {
        $ticketTypesByOrder = [];

        $concert = new Concert($concertId);
        $concert->load();

        $ticketTypesQuery = "SELECT * FROM `ticketsale_tickettypes` WHERE concertId=? ORDER BY price DESC";
        $ticketTypes = DBConnection::doQueryAndFetchAll($ticketTypesQuery, [$concertId]);

        $ordersQuery = "    SELECT DISTINCT b.id AS bestellingsnr,lastName,initials,`email`,street,houseNumber,houseNumberAddition,postcode,city,delivery,isDelivered,hasReservedSeats,isPaid,comments,deliveryByMember,deliveryMemberName,addressIsAbroad
                    FROM     `ticketsale_orders` AS b,
                            `ticketsale_orders_tickettypes` AS bk,
                            `ticketsale_tickettypes` AS k
                    WHERE b.id=bk.orderId AND k.id=bk.tickettypeId AND k.concertId=?
                    ORDER BY bestellingsnr;";
        $orders = DBConnection::doQueryAndFetchAll($ordersQuery, [$concertId]);

        $boughtTicketTypesQuery = "SELECT orderId,tickettypeId,amount
                    FROM     `ticketsale_orders_tickettypes`";
        $boughtTicketTypes = DBConnection::doQueryAndFetchAll($boughtTicketTypesQuery, [$concertId]);

        $this->addScript('/src/Ticketsale/ConcertOrderOverviewPage.js');

        parent::__construct('Overzicht bestellingen: ' . $concert->name);

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

        $this->render([
            'ticketTypes' => $ticketTypes,
            'concert' => $concert,
            'orders' => $orders,
            'ticketTypesByOrder' => $ticketTypesByOrder,
        ]);
    }
}