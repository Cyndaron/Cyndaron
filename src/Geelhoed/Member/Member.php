<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Member;

use Cyndaron\DBConnection;
use Cyndaron\Geelhoed\Graduation;
use Cyndaron\Geelhoed\Hour;
use Cyndaron\Geelhoed\MemberGraduation;
use Cyndaron\Model;
use Cyndaron\Photoalbum\Photoalbum;
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
     * @return Hour[]
     * @throws \Exception
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
            return;

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
}