<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Member;

use Cyndaron\DBConnection;
use Cyndaron\Geelhoed\Hour;
use Cyndaron\Model;
use Cyndaron\User\User;

class Member extends Model
{
    const TABLE = 'geelhoed_members';
    const TABLE_FIELDS = ['userId', 'parentEmail', 'phoneNumbers', 'isContestant', 'paymentMethod', 'iban', 'freeParticipation'];

    public int $userId;
    public string $parentEmail = '';
    protected string $phoneNumbers = '';
    public bool $isContestant = false;
    protected string $paymentMethod = 'incasso';
    public string $iban;
    public bool $freeParticipation = false;

    const PAYMENT_METHODS = [
        'incasso' => 'Automatische incasso',
        'jsf' => 'Jeugdsportfonds',
        'rekening' => 'Op rekening',
        'leergeld' => 'Stichting Leergeld',
    ];

    function getProfile(): User
    {
        $profile = new User($this->userId);
        $profile->load();
        return $profile;
    }

    /**
     * @return Hour[]
     * @throws \Exception
     */
    function getHours(): array
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

    function getPhoneNumbers(): array
    {
        if (!$this->phoneNumbers)
        {
            return [];
        }
        return explode(',', $this->phoneNumbers);
    }

    function setPhoneNumbers(array $numbers): void
    {
        $this->phoneNumbers = implode(',', $numbers);
    }

    function getEmail(): string
    {
        $profile = $this->getProfile();
        return $profile->email ?: $this->parentEmail;
    }
}