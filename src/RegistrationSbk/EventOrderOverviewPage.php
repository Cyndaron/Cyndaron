<?php
namespace Cyndaron\RegistrationSbk;

use Cyndaron\DBConnection;
use Cyndaron\Page;

class EventOrderOverviewPage extends Page
{
    public function __construct(Event $event)
    {
        $registrations = Registration::loadByEvent($event);
        $defData = [0 => ['amount' => 0, 'num' => 0], 1 => ['amount' => 0, 'num' => 0]];
        $totals = [
            'Alt' => $defData,
            'Bas' => $defData,
            'Sopraan' => $defData,
            'Tenor' => $defData,
            'Totaal' => $defData,
        ];

        $this->addScript('/src/RegistrationSbk/js/EventOrderOverviewPage.js');

        parent::__construct('Overzicht aanmeldingen: ' . $event->name);

        foreach ($registrations as $registration)
        {
            if ($registration->vocalRange)
            {
                $totals[$registration->vocalRange][$registration->isPaid]['num']++;
                $totals[$registration->vocalRange][$registration->isPaid]['amount'] += $registration->calculateTotal();
            }
        }
        foreach (['Alt', 'Bas', 'Sopraan', 'Tenor'] as $vocalRange)
        {
            $totals['Totaal'][0]['num'] += $totals[$vocalRange][0]['num'];
            $totals['Totaal'][0]['amount'] += $totals[$vocalRange][0]['amount'];
            $totals['Totaal'][1]['num'] += $totals[$vocalRange][1]['num'];
            $totals['Totaal'][1]['amount'] += $totals[$vocalRange][1]['amount'];
        }

        $this->render(compact('event', 'registrations', 'totals'));
    }
}