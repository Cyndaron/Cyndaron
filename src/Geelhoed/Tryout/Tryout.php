<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout;

use Cyndaron\Calendar\CalendarAppointment;
use Cyndaron\DBAL\Model;
use DateTime;
use function array_fill;
use function assert;
use function count;
use function is_array;
use function Safe\json_decode;

final class Tryout extends Model implements CalendarAppointment
{
    public const TABLE = 'geelhoed_volunteer_tot';
    public const TABLE_FIELDS = ['name', 'start', 'end', 'data', 'photoalbumLink'];

    public string $name = '';
    public DateTime $start;
    public DateTime $end;
    public string $data;
    public string $photoalbumLink;

    /**
     * @throws \Safe\Exceptions\JsonException
     * @return mixed[]
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

    /**
     * @return array<int, array<string, list<TryoutParticipation>>>
     */
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

        $participations = TryoutParticipation::fetchAll(['eventId = ?'], [$this->id]);
        foreach ($participations as $participation)
        {
            $decoded = $participation->getJsonData();
            $type = $participation->type;
            foreach ($decoded['rounds'] as $round)
            {
                $ret[$round][$type][] = $participation;
            }
        }

        return $ret;
    }

    public function getTryoutStatus(): TryoutStatus
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

        return new TryoutStatus($fullStatus, $fullRounds, $fullTypes);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getLocation(): string
    {
        return '';
    }

    public function getStart(): \DateTimeInterface
    {
        return $this->start;
    }

    public function getEnd(): \DateTimeInterface
    {
        return $this->end;
    }
}
