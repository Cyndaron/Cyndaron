<?php
namespace Cyndaron\Registration;

use Cyndaron\Page;

class OrderTicketsPage extends Page
{
    public function __construct(Event $event)
    {
        parent::__construct('Inschrijving: ' . $event->name);
        $this->addScript('/src/Registration/js/OrderTicketsPage.js');
        $this->addCss('/src/Registration/css/OrderTicketsPage.css');
        $this->render([
            'event' => $event,
            'ticketTypes' => EventTicketType::loadByEvent($event),
        ]);
    }
}