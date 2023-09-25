<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Volunteer;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\DBAL\Model;
use function is_array;
use function Safe\json_decode;
use function array_fill;
use function count;
use function assert;

final class Event extends Model
{
    public const TABLE = 'geelhoed_volunteer_event';
    public const TABLE_FIELDS = ['name', 'start', 'end', 'data'];

    public string $name = '';
    public string $start;
    public string $end;
    public string $data;

    /**
     * @return mixed[]
     * @throws \Safe\Exceptions\JsonException
     */
    public function getJsonData(): array
    {
        $decoded = json_decode($this->data, true);
        assert(is_array($decoded));
        return $decoded;
    }

    /**
     * @return array<int<0, max>, array<string, int>>
     */
    public function getNeededNumbers(): array
    {
        return [
            0 => [
                TryoutHelpType::TAFELMEDEWERKER->value => 20,
                TryoutHelpType::SCHEIDSRECHTER->value => 10,
                TryoutHelpType::GROEPJESBEGELEIDER->value => 18,
            ],
            1 => [
                TryoutHelpType::TAFELMEDEWERKER->value => 20,
                TryoutHelpType::SCHEIDSRECHTER->value => 10,
                TryoutHelpType::GROEPJESBEGELEIDER->value => 25,
            ],
            2 => [
                TryoutHelpType::TAFELMEDEWERKER->value => 20,
                TryoutHelpType::SCHEIDSRECHTER->value => 10,
                TryoutHelpType::GROEPJESBEGELEIDER->value => 0,
            ]
        ];
    }

    public function getTryoutNumRounds(): int
    {
        return 3;
    }

    public function getTryoutParticipationData(): array
    {
        $numRounds = $this->getTryoutNumRounds();
        $ret = [];
        for ($i = 0; $i < $numRounds; $i++)
        {
            $ret[$i] = [
                TryoutHelpType::TAFELMEDEWERKER->value => [],
                TryoutHelpType::SCHEIDSRECHTER->value => [],
                TryoutHelpType::GROEPJESBEGELEIDER->value => [],
            ];
        }

        $records = DBConnection::getPDO()->doQueryAndFetchAll('SELECT * FROM geelhoed_volunteer_event_participation WHERE eventId = ?', [$this->id]) ?: [];
        foreach ($records as $participation)
        {
            /** @var array $decoded */
            $decoded = json_decode($participation['data'], true);
            $type = $participation['type'];
            foreach ($decoded['rounds'] as $round)
            {
                $ret[$round][$type][] = $participation;
            }
        }

        return $ret;
    }

    public function getTryoutStatus(): array
    {
        $participationData = $this->getTryoutParticipationData();
        $neededNumbers = $this->getNeededNumbers();
        $fullStatus = array_fill(0, $this->getTryoutNumRounds(), []);
        $fullRounds = array_fill(0, $this->getTryoutNumRounds(), true);
        $fullTypes  = [
            TryoutHelpType::TAFELMEDEWERKER->value => true,
            TryoutHelpType::SCHEIDSRECHTER->value => true,
            TryoutHelpType::GROEPJESBEGELEIDER->value => true,
        ];

        foreach ($participationData as $round => $roundData)
        {
            foreach ($roundData as $type => $records)
            {
                $value = false;
                if (count($records) >= $neededNumbers[$round][$type])
                {
                    $value = true;
                }
                else
                {
                    $fullRounds[$round] = false;
                    $fullTypes[$type] = false;
                }

                $fullStatus[$round][$type] = $value;
            }
        }

        return [
            'fullStatus' => $fullStatus,
            'fullRounds' => $fullRounds,
            'fullTypes' => $fullTypes,
        ];
    }
}
