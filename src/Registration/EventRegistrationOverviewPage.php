<?php
declare(strict_types=1);

namespace Cyndaron\Registration;

use Cyndaron\DBAL\Connection;
use Cyndaron\Page\Page;
use function array_key_exists;

final class EventRegistrationOverviewPage extends Page
{
    public const TOTALS_FORMAT = [0 => ['amount' => 0, 'num' => 0], 1 => ['amount' => 0, 'num' => 0]];
    private readonly Connection $connection;

    public function __construct(Event $event, Connection $connection, RegistrationRepository $registrationRepository, RegistrationTicketTypeRepository $registrationTicketTypeRepository, EventTicketTypeRepository $eventTicketTypeRepository)
    {
        $this->connection = $connection;
        $ticketTypes = $eventTicketTypeRepository->loadByEvent($event);
        $registrations = $registrationRepository->loadByEvent($event);

        $this->addScript('/src/Registration/js/EventOrderOverviewPage.js');
        $this->addCss('/src/Ticketsale/css/Ticketsale.min.css');

        $this->title = 'Overzicht aanmeldingen: ' . $event->name;

        $totals = $this->calculateTotals($registrations, $registrationTicketTypeRepository);

        $ticketTypesByRegistration = $this->getTicketTypesByRegistration();
        $this->addTemplateVars(['event' => $event, 'ticketTypes' => $ticketTypes, 'ticketTypesByRegistration' => $ticketTypesByRegistration, 'registrations' => $registrations, 'totals' => $totals]);
    }

    /**
     * @return array<int, array<int, int>>
     */
    private function getTicketTypesByRegistration(): array
    {
        $boughtTicketTypes = $this->connection->doQueryAndFetchAll('SELECT * FROM `registration_orders_tickettypes`') ?: [];

        $ticketTypesByRegistration = [];
        foreach ($boughtTicketTypes as $boughtTicketType)
        {
            $registrationId = (int)$boughtTicketType['orderId'];
            $ticketType = (int)$boughtTicketType['tickettypeId'];
            if (!array_key_exists($registrationId, $ticketTypesByRegistration))
            {
                $ticketTypesByRegistration[$registrationId] = [];
            }

            $ticketTypesByRegistration[$registrationId][$ticketType] = (int)$boughtTicketType['amount'];
        }
        return $ticketTypesByRegistration;
    }

    /**
     * @param Registration[] $registrations
     * @param RegistrationTicketTypeRepository $registrationTicketTypeRepository
     * @return array<string, array<int, array{num: int, amount: float}>>
     */
    private function calculateTotals(array $registrations, RegistrationTicketTypeRepository $registrationTicketTypeRepository): array
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
                $totals[$registration->vocalRange][$registration->isPaid]['amount'] += $registration->calculateTotal($registrationTicketTypeRepository);
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
