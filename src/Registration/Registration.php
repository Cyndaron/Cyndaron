<?php
declare(strict_types=1);

namespace Cyndaron\Registration;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\Util\BuiltinSetting;
use Cyndaron\Util\Error\IncompleteData;
use Cyndaron\DBAL\Model;
use Cyndaron\Util\KnownShortCodes;
use Cyndaron\Util\MailFactory;
use Cyndaron\Util\Setting;
use Cyndaron\View\Template\TemplateRenderer;
use Symfony\Component\Mime\Address;
use function assert;
use function file_exists;
use function html_entity_decode;
use function floor;

final class Registration extends Model
{
    public const TABLE = 'registration_orders';

    #[DatabaseField]
    public int $eventId;
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

    /**
     * @param Event $event
     * @return self[]
     */
    public static function loadByEvent(Event $event): array
    {
        return self::fetchAll(['eventId = ?'], [$event->id], 'ORDER BY id');
    }

    public function getEvent(): Event
    {
        $event = Event::fetchById($this->eventId);
        assert($event !== null);
        return $event;
    }

    /**
     * @param MailFactory $mailFactory
     * @param float $registrationTotal
     * @param array<int, int> $registrationTicketTypes
     * @param TemplateRenderer $templateRenderer
     * @return bool
     */
    public function sendIntroductionMail(MailFactory $mailFactory, float $registrationTotal, array $registrationTicketTypes, TemplateRenderer $templateRenderer): bool
    {
        $event = $this->getEvent();

        $ticketTypes = EventTicketType::loadByEvent($event);
        $lunchText = ($this->lunch) ? $this->lunchType : 'Geen';
        $extraFields = [
            'Geboortejaar' => $this->birthYear,
            'Straatnaam en huisnummer' => "$this->street $this->houseNumber $this->houseNumberAddition",
            'Postcode' => $this->postcode,
            'Woonplaats' => $this->city,
            'Opmerkingen' => $this->comments,
        ];

        $templateFile = 'Registration/ConfirmationMail';
        $shortCode = Setting::get(BuiltinSetting::SHORT_CODE);
        if ($shortCode === KnownShortCodes::VOV)
        {
            $templateFile = 'Registration/ConfirmationMailVOV';
            if (file_exists(__DIR__ . '/templates/ConfirmationMailVOV-' . $event->id . '.blade.php'))
            {
                $templateFile = 'Registration/ConfirmationMailVOV-' . $event->id;
            }
        }

        $registration = $this;
        $args = ['registration' => $registration, 'event' => $event, 'registrationTotal' => $registrationTotal, 'ticketTypes' => $ticketTypes, 'registrationTicketTypes' => $registrationTicketTypes, 'lunchText' => $lunchText, 'extraFields' => $extraFields];
        $text = $templateRenderer->render($templateFile, $args);
        // We're sending a plaintext mail, so avoid displaying html entities.
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        $mail = $mailFactory->createMailWithDefaults(new Address($this->email), 'Inschrijving ' . $event->name, $text);
        return $mail->send();
    }

    public function setIsPaid(MailFactory $mailFactory): bool
    {
        if ($this->id === null)
        {
            throw new IncompleteData('ID is null!');
        }

        $event = $this->getEvent();

        $this->isPaid = true;
        $this->save();

        $organisation = Setting::get(BuiltinSetting::ORGANISATION);
        $text = "Hartelijk dank voor uw inschrijving bij $organisation. Wij hebben uw betaling in goede orde ontvangen.\n";
        if (Setting::get(BuiltinSetting::SHORT_CODE) !== KnownShortCodes::VOV)
        {
            $text .= 'Eventueel bestelde kaarten voor vrienden en familie zullen op de avond van het concert voor u klaarliggen bij de kassa.';
        }

        $mail = $mailFactory->createMailWithDefaults(
            new Address($this->email),
            'Betalingsbevestiging ' . $event->name,
            $text
        );
        return $mail->send();
    }

    /**
     * @param array<int, int> $registrationTicketTypes
     * @return float
     */
    public function calculateTotal(array $registrationTicketTypes = []): float
    {
        $event = $this->getEvent();
        $registrationTotal = 0;
        if ($this->registrationGroup === 3)
        {
            $registrationTotal += $event->registrationCost3;
        }
        elseif ($this->registrationGroup === 2)
        {
            $registrationTotal += $event->registrationCost2;
        }
        elseif ($this->registrationGroup === 1)
        {
            $registrationTotal += $event->registrationCost1;
        }
        else
        {
            $registrationTotal += $event->registrationCost0;
        }

        if ($this->lunch)
        {
            $registrationTotal += $event->lunchCost;
        }

        $ticketTypes = EventTicketType::loadByEvent($event);
        foreach ($ticketTypes as $ticketType)
        {
            $num = $registrationTicketTypes[$ticketType->id] ?? 0;
            if ($ticketType->discountPer5)
            {
                $num -= floor($num / 5);
            }

            $registrationTotal +=  $num * $ticketType->price;
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
        return 'Onbekend';
    }

    public function shouldPay(): bool
    {
        return !$this->isPaid && $this->approvalStatus === RegistrationApprovalStatus::APPROVED;
    }
}
