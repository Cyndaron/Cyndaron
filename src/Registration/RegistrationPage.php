<?php
namespace Cyndaron\Registration;

use Cyndaron\View\Page;
use Cyndaron\Util\Setting;

final class RegistrationPage extends Page
{
    public function __construct(Event $event)
    {
        parent::__construct('Aanmelding: ' . $event->name);

        $this->addScript('/src/Registration/js/RegistrationPage.js');
        $this->addCss('/src/Registration/css/RegistrationPage.css');

        $organisation = Setting::get('organisation');

        if ($organisation === 'Vlissingse Oratorium Vereniging')
        {
            $this->template = 'Registration/RegistrationPageVOV';
            $this->addScript('/src/Registration/js/RegistrationPageVOV.js');
        }
        elseif ($organisation === 'Stichting Bijzondere Koorprojecten')
        {
            $this->template = 'Registration/RegistrationPageSBK';
        }

        $this->addTemplateVars([
            'event' => $event,
            'ticketTypes' => EventTicketType::loadByEvent($event),
        ]);
    }
}
