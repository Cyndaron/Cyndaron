<?php
declare(strict_types=1);

namespace Cyndaron\Registration;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;
use function floor;

final class Registration extends Model
{
    public const TABLE = 'registration_orders';

    #[DatabaseField(dbName: 'eventId')]
    public Event $event;
    #[DatabaseField]
    public string $lastName;
    #[DatabaseField]
    public string $initials;
    #[DatabaseField]
    public int $registrationGroup = 0;
    #[DatabaseField]
    public string $vocalRange;
    #[DatabaseField]
    public int|null $birthYear = null;
    #[DatabaseField]
    public bool $lunch = false;
    #[DatabaseField]
    public string $lunchType = '';
    #[DatabaseField]
    public bool $bhv = false;
    #[DatabaseField]
    public bool $masterclass = false;
    #[DatabaseField]
    public bool $kleinkoor = false;
    #[DatabaseField]
    public string $kleinkoorExplanation = '';
    #[DatabaseField]
    public int $participatedBefore = 0;
    #[DatabaseField]
    public int $numPosters = 0;
    #[DatabaseField]
    public string $email = '';
    #[DatabaseField]
    public string $phone = '';
    #[DatabaseField]
    public string $street;
    #[DatabaseField]
    public int $houseNumber;
    #[DatabaseField]
    public string $houseNumberAddition;
    #[DatabaseField]
    public string $postcode;
    #[DatabaseField]
    public string $city = '';
    #[DatabaseField]
    public string $currentChoir = '';
    #[DatabaseField]
    public string $choirPreference = '';
    #[DatabaseField]
    public int $choirExperience = 0;
    #[DatabaseField]
    public bool $performedBefore = false;
    #[DatabaseField]
    public string $comments;
    #[DatabaseField]
    public RegistrationApprovalStatus $approvalStatus = RegistrationApprovalStatus::UNDECIDED;
    #[DatabaseField]
    public bool $isPaid = false;

    public function calculateTotal(RegistrationTicketTypeRepository $registrationTicketTypeRepository): float
    {
        $registrationTotal = 0;
        if ($this->registrationGroup === 3)
        {
            $registrationTotal += $this->event->registrationCost3;
        }
        elseif ($this->registrationGroup === 2)
        {
            $registrationTotal += $this->event->registrationCost2;
        }
        elseif ($this->registrationGroup === 1)
        {
            $registrationTotal += $this->event->registrationCost1;
        }
        else
        {
            $registrationTotal += $this->event->registrationCost0;
        }

        if ($this->lunch)
        {
            $registrationTotal += $this->event->lunchCost;
        }

        $rtts = $registrationTicketTypeRepository->loadByRegistration($this);
        foreach ($rtts as $rtt)
        {
            $num = $rtt->amount;
            if ($rtt->ticketType->discountPer5)
            {
                $num -= floor($num / 5);
            }

            $registrationTotal += $num * $rtt->ticketType->price;
        }
        return $registrationTotal;
    }

    public function getStatus(): string
    {
        switch ($this->approvalStatus)
        {
            case RegistrationApprovalStatus::UNDECIDED:
                return 'Nieuw';
            case RegistrationApprovalStatus::APPROVED:
                if ($this->isPaid)
                {
                    return 'Toegelaten, betaald';
                }
                return 'Toegelaten, niet betaald';

            case RegistrationApprovalStatus::DISAPPROVED:
                if ($this->isPaid)
                {
                    return 'Afgewezen, betaald';
                }

                return 'Afgewezen, niet betaald';
        }
    }

    public function shouldPay(): bool
    {
        return !$this->isPaid && $this->approvalStatus === RegistrationApprovalStatus::APPROVED;
    }
}
