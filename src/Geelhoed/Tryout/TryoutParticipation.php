<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout;

use Cyndaron\DBAL\FileCachedModel;
use Cyndaron\DBAL\Model;
use Safe\Exceptions\JsonException;
use function Safe\json_decode;

final class TryoutParticipation extends Model
{
    use FileCachedModel;

    public const TABLE = 'geelhoed_volunteer_tot_participation';
    // Override to include the fields for that particular model
    public const TABLE_FIELDS = ['eventId', 'name', 'email', 'phone', 'type', 'data', 'comments'];

    public int $eventId;
    public string $name;
    public string $email;
    public string $phone;
    public string $type;
    public string $data;
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
