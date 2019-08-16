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
        $registrations = Order::loadByEvent($event);
        $boughtTicketTypes = DBConnection::doQueryAndFetchAll('SELECT * FROM `registration_orders_tickettypes`');

        $this->addScript('/src/Registration/js/EventOrderOverviewPage.js');

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