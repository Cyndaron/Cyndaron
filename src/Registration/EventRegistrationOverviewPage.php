<?php
namespace Cyndaron\Registration;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\View\Page;
use Cyndaron\Util\Setting;
use function array_key_exists;

final class EventRegistrationOverviewPage extends Page
{
    public const TOTALS_FORMAT = [0 => ['amount' => 0, 'num' => 0], 1 => ['amount' => 0, 'num' => 0]];

    public function __construct(Event $event)
    {
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

        $this->addScript('/src/Registration/js/EventOrderOverviewPage.js');

        parent::__construct('Overzicht aanmeldingen: ' . $event->name);

        $ticketTypesByRegistration = $this->getTicketTypesByRegistration();
        $totals = $this->calculateTotals($registrations, $ticketTypesByRegistration);

        $this->addTemplateVars(['event' => $event, 'ticketTypes' => $ticketTypes, 'ticketTypesByRegistration' => $ticketTypesByRegistration, 'registrations' => $registrations, 'totals' => $totals]);
    }

    /**
     * @return array
     */
    private function getTicketTypesByRegistration(): array
    {
        $boughtTicketTypes = DBConnection::doQueryAndFetchAll('SELECT * FROM `registration_orders_tickettypes`') ?: [];

        $ticketTypesByRegistration = [];
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
        return $ticketTypesByRegistration;
    }

    /**
     * @param Registration[] $registrations
     * @param array $ticketTypesByRegistration
     * @return array
     */
    private function calculateTotals(array $registrations, array $ticketTypesByRegistration): array
    {
        $totals = [
            'Alt' => self::TOTALS_FORMAT,
            'Bas' => self::TOTALS_FORMAT,
            'Sopraan' => self::TOTALS_FORMAT,
            'Tenor' => self::TOTALS_FORMAT,
            'Totaal' => self::TOTALS_FORMAT,
        ];
        foreach ($registrations as $registration)
        {
            if ($registration->vocalRange !== '')
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

        return $totals;
    }
}
