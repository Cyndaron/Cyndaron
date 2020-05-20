<?php
namespace Cyndaron\Registration;

use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\Setting;

class EventRegistrationOverviewPage extends Page
{
    public function __construct(Event $event)
    {
        $ticketTypesByRegistration = [];

        switch (Setting::get('organisation'))
        {
            case 'Vlissingse Oratorium Vereniging':
                $this->template = 'Registration/EventRegistrationOverviewPageVOV';
                break;
            case 'Stichting Bijzondere Koorprojecten':
                $this->template = 'Registration/EventRegistrationOverviewPageSBK';
                break;
        }

        $ticketTypes = EventTicketType::loadByEvent($event);
        $registrations = Registration::loadByEvent($event);
        $boughtTicketTypes = DBConnection::doQueryAndFetchAll('SELECT * FROM `registration_orders_tickettypes`');
        $defData = [0 => ['amount' => 0, 'num' => 0], 1 => ['amount' => 0, 'num' => 0]];
        $totals = [
            'Alt' => $defData,
            'Bas' => $defData,
            'Sopraan' => $defData,
            'Tenor' => $defData,
            'Totaal' => $defData,
        ];

        $this->addScript('/src/Registration/js/EventOrderOverviewPage.js');

        parent::__construct('Overzicht aanmeldingen: ' . $event->name);

        foreach ($boughtTicketTypes as $boughtTicketType)
        {
            $registrationId = $boughtTicketType['registrationId'];
            $ticketType = $boughtTicketType['tickettypeId'];
            if (!array_key_exists($registrationId, $ticketTypesByRegistration))
            {
                $ticketTypesByRegistration[$registrationId] = [];
            }

            $ticketTypesByRegistration[$registrationId][$ticketType] = $boughtTicketType['amount'];
        }
        foreach ($registrations as $registration)
        {
            if ($registration->vocalRange)
            {
                $totals[$registration->vocalRange][$registration->isPaid]['num']++;
                $totals[$registration->vocalRange][$registration->isPaid]['amount'] += $registration->calculateTotal($ticketTypesByRegistration[$registration->id] ?? []);
            }
        }
        foreach (['Alt', 'Bas', 'Sopraan', 'Tenor'] as $vocalRange)
        {
            $totals['Totaal'][0]['num'] += $totals[$vocalRange][0]['num'];
            $totals['Totaal'][0]['amount'] += $totals[$vocalRange][0]['amount'];
            $totals['Totaal'][1]['num'] += $totals[$vocalRange][1]['num'];
            $totals['Totaal'][1]['amount'] += $totals[$vocalRange][1]['amount'];
        }

        $this->addTemplateVars(compact('event', 'ticketTypes', 'ticketTypesByRegistration', 'registrations', 'totals'));
    }
}
