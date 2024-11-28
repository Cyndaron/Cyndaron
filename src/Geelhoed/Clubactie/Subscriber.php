<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Clubactie;

use Cyndaron\DBAL\FileCachedModel;
use Cyndaron\DBAL\Model;
use function implode;

final class Subscriber extends Model
{
    use FileCachedModel;

    public const TABLE = 'geelhoed_clubactie_subscriber';
    public const TABLE_FIELDS = ['firstName', 'tussenvoegsel', 'lastName', 'email', 'phone', 'numSoldTickets', 'soldTicketsAreVerified', 'hash'];

    public string $firstName = '';
    public string $tussenvoegsel = '';
    public string $lastName = '';
    public string $email = '';
    public string $phone = '';

    public int $numSoldTickets = 0;
    public bool $soldTicketsAreVerified = false;
    public string $hash = '';

    public static function fetchByHash(string $hash): self|null
    {
        if ($hash === '')
        {
            return null;
        }
        return self::fetch(['hash = ?'], [$hash]);
    }

    public function getFullName(): string
    {
        $nameParts = [$this->firstName];
        if ($this->tussenvoegsel !== '')
        {
            $nameParts[] = $this->tussenvoegsel;
        }

        $nameParts[] = $this->lastName;

        return implode(' ', $nameParts);
    }
}
