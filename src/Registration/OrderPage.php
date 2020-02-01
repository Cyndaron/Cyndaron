<?php
namespace Cyndaron\Registration;

use Cyndaron\Page;
use Cyndaron\Setting;

class OrderPage extends Page
{
    public function __construct(Event $event)
    {
        parent::__construct('Inschrijving: ' . $event->name);

        $this->addScript('/src/Registration/js/OrderPage.js');
        $this->addCss('/src/Registration/css/OrderPage.css');

        if (Setting::get('organisation') === 'Vlissingse Oratorium Vereniging')
        {
            $this->template = 'Registration/OrderPageVOV';
            $this->addScript('/src/Registration/js/OrderPageVOV.js');
        }

        $this->render([
            'event' => $event,
            'ticketTypes' => EventTicketType::loadByEvent($event),
        ]);
    }
}