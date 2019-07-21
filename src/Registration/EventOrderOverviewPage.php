<?php
namespace Cyndaron\Registration;

use Cyndaron\DBConnection;
use Cyndaron\Page;

class EventOrderOverviewPage extends Page
{
    public function __construct(Event $event)
    {
        $ticketTypesByOrder = [];

        $ticketTypes = EventTicketType::loadByEvent($event);

        $ordersQuery = "SELECT DISTINCT ro.id AS orderId,lastName,initials,vocalRange,`email`,street,houseNumber,houseNumberAddition,postcode,city,isPaid,lunch,comments
                    FROM     `registration_orders` AS ro
                    WHERE  ro.eventId=?
                    ORDER BY orderId;";
        $registrations = DBConnection::doQueryAndFetchAll($ordersQuery, [$event->id]);

        $boughtTicketTypesQuery = "SELECT orderId,tickettypeId,amount
                    FROM     `registration_orders_tickettypes`";
        $boughtTicketTypes = DBConnection::doQueryAndFetchAll($boughtTicketTypesQuery, [$event->id]);

        $this->addScript('/src/Registration/EventOrderOverviewPage.js');

        parent::__construct('Overzicht inschrijvingen: ' . $event->name);

        foreach ($boughtTicketTypes as $boughtTicketType)
        {
            $orderId = $boughtTicketType['orderId'];
            $ticketType = $boughtTicketType['tickettypeId'];
            if (!array_key_exists($orderId, $ticketTypesByOrder))
            {
                $ticketTypesByOrder[$orderId] = [];
            }

            $ticketTypesByOrder[$orderId][$ticketType] = $boughtTicketType['amount'];
        }

        $this->render([
            'event' => $event,
            'ticketTypes' => $ticketTypes,
            'ticketTypesByOrder' => $ticketTypesByOrder,
            'registrations' => $registrations,
        ]);
    }
}