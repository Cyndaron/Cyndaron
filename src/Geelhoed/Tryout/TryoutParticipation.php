<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\FileCachedModel;
use Cyndaron\DBAL\Model;
use Safe\Exceptions\JsonException;
use function Safe\json_decode;

final class TryoutParticipation extends Model
{
    use FileCachedModel;

    public const TABLE = 'geelhoed_volunteer_tot_participation';

    #[DatabaseField]
    public int $eventId;
    #[DatabaseField]
    public string $name;
    #[DatabaseField]
    public string $email;
    #[DatabaseField]
    public string $phone;
    #[DatabaseField]
    public string $type;
    #[DatabaseField]
    public string $data;
    #[DatabaseField]
    public string $comments;

    /**
     * @throws JsonException
     * @return array{ rounds: list<int> }
     */
    public function getJsonData(): array
    {
        /** @var array{ rounds: list<int> } $result */
        $result = json_decode($this->data, true);
        return $result;
    }
}
