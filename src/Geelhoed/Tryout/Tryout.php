<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout;

use Cyndaron\Calendar\CalendarAppointment;
use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;
use Cyndaron\Location\Location;
use DateTime;
use function assert;
use function is_array;
use function Safe\json_decode;

final class Tryout extends Model implements CalendarAppointment
{
    public const TABLE = 'geelhoed_volunteer_tot';

    #[DatabaseField]
    public string $name = '';
    #[DatabaseField(dbName: 'locationId')]
    public Location|null $location = null;
    #[DatabaseField]
    public DateTime $start;
    #[DatabaseField]
    public DateTime $end;
    #[DatabaseField]
    public string $data;
    #[DatabaseField]
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
            ],
        ];
    }

    public function getNumRounds(): int
    {
        return 3;
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
        return $this->location ? $this->location->getName() : '';
    }

    public function getStart(): \DateTimeInterface
    {
        return $this->start;
    }

    public function getEnd(): \DateTimeInterface
    {
        return $this->end;
    }

    public function getUrl(): string|null
    {
        return null;
    }
}
