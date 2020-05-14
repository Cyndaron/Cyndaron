<?php
namespace Cyndaron\Registration;

use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\Setting;

class EventOrderOverviewPage extends Page
{
    public function __construct(Event $event)
    {
        $ticketTypesByOrder = [];

        if (Setting::get('organisation') === 'Vlissingse Oratorium Vereniging')
        {
            $this->template = 'Registration/EventOrderOverviewPageVOV';
        }

        $ticketTypes = EventTicketType::loadByEvent($event);
        $registrations = Order::loadByEvent($event);
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
        foreach ($registrations as $registration)
        {
            if ($registration->vocalRange)
            {
                $totals[$registration->vocalRange][$registration->isPaid]['num']++;
                $totals[$registration->vocalRange][$registration->isPaid]['amount'] += $registration->calculateTotal($ticketTypesByOrder[$registration->id] ?? []);
            }
        }
        foreach (['Alt', 'Bas', 'Sopraan', 'Tenor'] as $vocalRange)
        {
            $totals['Totaal'][0]['num'] += $totals[$vocalRange][0]['num'];
            $totals['Totaal'][0]['amount'] += $totals[$vocalRange][0]['amount'];
            $totals['Totaal'][1]['num'] += $totals[$vocalRange][1]['num'];
            $totals['Totaal'][1]['amount'] += $totals[$vocalRange][1]['amount'];
        }

        $this->addTemplateVars(compact('event', 'ticketTypes', 'ticketTypesByOrder', 'registrations', 'totals'));
    }
}