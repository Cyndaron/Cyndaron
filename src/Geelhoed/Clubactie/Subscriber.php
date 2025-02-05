<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Clubactie;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\FileCachedModel;
use Cyndaron\DBAL\Model;
use function implode;

final class Subscriber extends Model
{
    use FileCachedModel;

    public const TABLE = 'geelhoed_clubactie_subscriber';

    #[DatabaseField]
    public string $firstName = '';
    #[DatabaseField]
    public string $tussenvoegsel = '';
    #[DatabaseField]
    public string $lastName = '';
    #[DatabaseField]
    public string $email = '';
    #[DatabaseField]
    public string $phone = '';

    #[DatabaseField]
    public int $numSoldTickets = 0;
    #[DatabaseField]
    public bool $soldTicketsAreVerified = false;
    #[DatabaseField]
    public bool $emailSent = false;
    #[DatabaseField]
    public string $hash = '';

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
