<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed;

use Cyndaron\DBConnection;
use Cyndaron\Model;
use Cyndaron\User\User;

class Member extends Model
{
    const TABLE = 'geelhoed_members';
    const TABLE_FIELDS = ['userId', 'email', 'phoneNumbers', 'isContestant'];

    public $userId = null;
    public $email = '';
    protected $phoneNumbers = '';
    public $isContestant = false;

    function getProfile(): User
    {
        $profile = new User((int)$this->userId);
        $profile->load();
        return $profile;
    }

    /**
     * @return Hour[]
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
        return $this->email;
    }
}