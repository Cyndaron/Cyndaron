<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Member;

use Cyndaron\DBConnection;
use Cyndaron\Geelhoed\Graduation;
use Cyndaron\Geelhoed\Hour\Hour;
use Cyndaron\Geelhoed\MemberGraduation;
use Cyndaron\Geelhoed\Sport;
use Cyndaron\Model;
use Cyndaron\User\User;
use Cyndaron\Util;

class Member extends Model
{
    public const TABLE = 'geelhoed_members';
    public const TABLE_FIELDS = ['userId', 'parentEmail', 'phoneNumbers', 'isContestant', 'paymentMethod', 'iban', 'paymentProblem', 'paymentProblemNote', 'freeParticipation', 'temporaryStop', 'joinedAt', 'jbnNumber', 'jbnNumberLocation'];

    public int $userId;
    public string $parentEmail = '';
    public string $phoneNumbers = '';
    public bool $isContestant = false;
    public string $paymentMethod = 'incasso';
    public string $iban = '';
    public bool $paymentProblem = false;
    public string $paymentProblemNote = '';
    public bool $freeParticipation = false;
    public bool $temporaryStop = false;
    public ?string $joinedAt = null;
    public string $jbnNumber = '';
    public string $jbnNumberLocation = '';

    public const PAYMENT_METHODS = [
        'incasso' => 'Automatische incasso',
        'jsf' => 'Jeugdsportfonds',
        'rekening' => 'Op rekening',
        'leergeld' => 'Stichting Leergeld',
    ];

    public function getProfile(): User
    {
        $profile = new User($this->userId);
        $profile->load();
        return $profile;
    }

    /**
     * @throws \Exception
     * @return Hour[]
     */
    public function getHours(): array
    {
        $sql = 'SELECT * FROM geelhoed_hours WHERE id IN (SELECT hourId FROM geelhoed_members_hours WHERE memberId = ?)';
        $hoursArr = DBConnection::doQueryAndFetchAll($sql, [$this->id]);
        $hours = [];
        foreach ($hoursArr as $hourArr)
        {
            $hours[] = Hour::loadFromDatabase((int)$hourArr['id']);
        }
        return $hours;
    }

    /**
     * @param Hour[] $hours
     */
    public function setHours(array $hours): void
    {
        DBConnection::doQuery('DELETE FROM geelhoed_members_hours WHERE memberId = ?', [$this->id]);

        if (empty($hours))
        {
            return;
        }

        $sql = 'INSERT INTO geelhoed_members_hours(memberId, hourId) VALUES ';
        foreach ($hours as $hour)
        {
            $sql .= "({$this->id}, {$hour->id}), ";
        }

        $sql = trim($sql, ' ,');
        DBConnection::doQuery($sql);
    }

    public function getPhoneNumbers(): array
    {
        if (!$this->phoneNumbers)
        {
            return [];
        }
        return explode(',', $this->phoneNumbers);
    }

    public function setPhoneNumbers(array $numbers): void
    {
        $this->phoneNumbers = implode(',', $numbers);
    }

    public function getEmail(): string
    {
        $profile = $this->getProfile();
        return $profile->email ?: $this->parentEmail;
    }

    /**
     * @return MemberGraduation[]
     */
    public function getMemberGraduations(): array
    {
        return MemberGraduation::fetchAllByMember($this);
    }

    public function isSenior(): bool
    {
        $dateOfBirth = $this->getProfile()->getDateOfBirth();
        if ($dateOfBirth === null)
        {
            return true;
        }

        $startOfNextQuarter = Util::getStartOfNextQuarter();
        $diff = $startOfNextQuarter->diff($dateOfBirth);
        return $diff->format('%y') >= 15;
    }

    /**
     * @throws \Exception
     * @return Sport[]
     */
    public function getSports(): array
    {
        $sports = [];
        foreach ($this->getHours() as $hour)
        {
            $sport = $hour->getSport();
            $sports[$sport->id] = $sport;
        }

        return $sports;
    }

    public function getHighestGraduation(Sport $sport): ?Graduation
    {
        $graduations = MemberGraduation::fetchAllByMember($this);
        // Results are ordered by date, so the reverse it to start with the highest ones.
        foreach (array_reverse($graduations) as $memberGraduation)
        {
            $graduation = $memberGraduation->getGraduation();
            if ($graduation->getSport()->id === $sport->id)
            {
                return $graduation;
            }
        }

        return null;
    }

    /**
     * Calculate monthly total from the start of the next quarter.
     *
     * @throws \Exception
     * @return float
     */
    public function getMonthlyFee(): float
    {
        $isSenior = $this->isSenior();
        $sports = $this->getSports();
        if (count($sports) === 0)
        {
            return 0.00;
        }
        if (count($sports) === 1)
        {
            $sport = reset($sports);
            if ($isSenior)
            {
                return $sport->seniorFee;
            }

            return $sport->juniorFee;
        }

        $highestFee = 0.00;
        foreach ($sports as $sport)
        {
            $fee = $isSenior ? $sport->seniorFee : $sport->juniorFee;
            if ($fee > $highestFee)
            {
                $highestFee = $fee;
            }
        }

        return $highestFee + 5.00;
    }

    /**
     * Calculate quarterly total from the start of the next quarter.
     *
     * @throws \Exception
     * @return float
     */
    public function getQuarterlyFee(): float
    {
        return $this->getMonthlyFee() * 3;
    }

    /**
     * @param array $where
     * @param array $args
     * @param string $afterWhere
     * @return static[]
     */
    public static function fetchAll(array $where = [], array $args = [], string $afterWhere = 'ORDER BY lastname,tussenvoegsel,firstname'): array
    {
        $whereString = '';
        if (count($where) > 0)
        {
            $whereString = 'WHERE ' . implode(' AND ', $where);
        }
        $results = DBConnection::doQueryAndFetchAll('SELECT * FROM `geelhoed_members` gm INNER JOIN `users` u on gm.userId = u.id ' . $whereString . ' ' . $afterWhere, $args);
        $ret = [];
        if ($results)
        {
            foreach ($results as $result)
            {
                $obj = new static((int)$result['id']);
                $obj->updateFromArray($result);
                $ret[] = $obj;
            }
        }

        return $ret;
    }

    /**
     * @param Hour $hour
     * @return Member[]
     */
    public static function fetchAllByHour(Hour $hour): array
    {
        $results = DBConnection::doQueryAndFetchAll('SELECT * FROM `geelhoed_members` WHERE id IN (SELECT memberId FROM geelhoed_members_hours WHERE hourId = ?)', [$hour->id]);
        $ret = [];
        if ($results)
        {
            foreach ($results as $result)
            {
                $obj = new static((int)$result['id']);
                $obj->updateFromArray($result);
                $ret[] = $obj;
            }
        }

        return $ret;
    }

    public static function loadFromProfile(User $profile): ?self
    {
        $results = self::fetchAll(['userId = ?'], [$profile->id]);
        if (count($results) <= 0)
        {
            return null;
        }

        return reset($results);
    }

    public static function loadFromLoggedInUser(): ?self
    {
        if (!empty($_SESSION['geelhoedMemberProfile']))
        {
            return $_SESSION['geelhoedMemberProfile'];
        }

        if (!User::isLoggedIn())
        {
            return null;
        }

        $profile = $_SESSION['profile'];
        if ($profile === null)
        {
            return null;
        }

        $member = self::loadFromProfile($profile);
        if ($member !== null)
        {
            $_SESSION['geelhoedMemberProfile'] = $member;
        }

        return $member;
    }
}
