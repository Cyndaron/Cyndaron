<?php
namespace Cyndaron\RegistrationSbk;

use Cyndaron\Page;

class RegisterPage extends Page
{
    public function __construct(Event $event)
    {
        parent::__construct('Aanmelding: ' . $event->name);
        $this->renderAndEcho([
            'event' => $event,
        ]);
    }
}