<?php
declare (strict_types = 1);

namespace Cyndaron\Registration;

use Cyndaron\Model;
use Cyndaron\Setting;
use Cyndaron\Template\Template;
use \Exception;

class Order extends Model
{
    public const TABLE = 'registration_orders';
    public const TABLE_FIELDS = ['eventId', 'lastName', 'initials', 'registrationGroup', 'vocalRange', 'birthYear', 'lunch', 'lunchType', 'bhv', 'kleinkoor', 'kleinkoorExplanation', 'participatedBefore', 'numPosters', 'email', 'street', 'houseNumber', 'houseNumberAddition', 'postcode', 'city', 'comments', 'isPaid', 'currentChoir', 'choirPreference', 'approvalStatus'];

    public const APPROVAL_UNDECIDED = 0;
    public const APPROVAL_APPROVED = 1;
    public const APPROVAL_DISAPPROVED = 2;

    public int $eventId;
    public string $lastName;
    public string $initials;
    public int $registrationGroup = 0;
    public string $vocalRange;
    public ?int $birthYear = null;
    public bool $lunch = false;
    public string $lunchType = '';
    public bool $bhv = false;
    public bool $kleinkoor = false;
    public string $kleinkoorExplanation = '';
    public bool $participatedBefore = false;
    public int $numPosters = 0;
    public string $email;
    public string $street;
    public int $houseNumber;
    public string $houseNumberAddition;
    public string $postcode;
    public string $city;
    public string $comments;
    public bool $isPaid = false;
    public string $currentChoir = '';
    public string $choirPreference = '';
    public int $approvalStatus = self::APPROVAL_UNDECIDED;

    public static function loadByEvent(Event $event): array
    {
        return static::fetchAll(['eventId = ?'], [$event->id], 'ORDER BY id');
    }

    public function getEvent(): Event
    {
        return Event::loadFromDatabase((int)$this->eventId);
    }

    /**
     * @param float $orderTotal
     * @param array $orderTicketTypes
     * @return bool
     */
    public function sendConfirmationMail(float $orderTotal, array $orderTicketTypes): bool
    {
        $order = $this;
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
        if (Setting::get('organisation') === 'Vlissingse Oratorium Vereniging')
        {
            $templateFile = 'Registration/ConfirmationMailVOV';
        }

        $template = new Template();
        $args = compact('order', 'event', 'orderTotal', 'ticketTypes', 'orderTicketTypes', 'lunchText', 'extraFields');
        $text = $template->render($templateFile, $args);
        // We're sending a plaintext mail, so avoid displaying html entities.
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        return Util::mail($this->email, 'Inschrijving ' . $event->name, $text);
    }

    public function setIsPaid(): bool
    {
        if ($this->id === null)
        {
            throw new Exception('ID is null!');
        }

        $this->isPaid = true;
        $this->save();

        $organisation = Setting::get('organisation');
        $text = "Hartelijk dank voor uw inschrijving bij $organisation. Wij hebben uw betaling in goede orde ontvangen.\n";
        if ($organisation !== 'Vlissingse Oratorium Vereniging')
        {
            $text .= 'Eventueel bestelde kaarten voor vrienden en familie zullen op de avond van het concert voor u klaarliggen bij de kassa.';
        }

        return Util::mail($this->email, 'Betalingsbevestiging', $text);
    }

    public function calculateTotal(array $orderTicketTypes = []): float
    {
        $event = $this->getEvent();
        $orderTotal = 0;
        if ($this->registrationGroup === 2)
        {
            $orderTotal += $event->registrationCost2;
        }
        elseif ($this->registrationGroup === 1)
        {
            $orderTotal += $event->registrationCost1;
        }
        else
        {
            $orderTotal += $event->registrationCost0;
        }

        if ($this->lunch)
        {
            $orderTotal += $event->lunchCost;
        }

        $ticketTypes = EventTicketType::loadByEvent($event);
        foreach ($ticketTypes as $ticketType)
        {
            $num = $orderTicketTypes[$ticketType->id] ?? 0;
            if ($ticketType->discountPer5)
            {
                $num -= floor($num / 5);
            }

            $orderTotal +=  $num * $ticketType->price;
        }
        return $orderTotal;
    }

    public function getStatus(): string
    {
        switch ($this->approvalStatus)
        {
            case self::APPROVAL_UNDECIDED:
                return 'Nieuw';
            case self::APPROVAL_APPROVED:
                if ($this->isPaid)
                    return 'Toegelaten, betaald';
                else
                    return 'Toegelaten, niet betaald';
            case self::APPROVAL_DISAPPROVED:
                if ($this->isPaid)
                    return 'Afgewezen, betaald';
                else
                    return 'Afgewezen, niet betaald';
        }
        return 'Onbekend';
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function setApproved(): bool
    {
        if ($this->id === null)
        {
            throw new Exception('ID is null!');
        }

        $this->approvalStatus = self::APPROVAL_APPROVED;
        $this->save();

        $event = $this->getEvent();
        $orderTotal = $this->calculateTotal();

        $text = '';

        return Util::mail($this->email, 'Aanmelding ' . $event->name . ' goedgekeurd', $text);
    }

    public function setDisapproved(): bool
    {
        if ($this->id === null)
        {
            throw new Exception('ID is null!');
        }

        $this->approvalStatus = self::APPROVAL_DISAPPROVED;
        $this->save();

        $event = $this->getEvent();

        if ($event->requireApproval)
        {
            $text = '';
        }
        else
        {
            $text = 'Uw bestelling is geannuleerd. Eventuele betalingen zullen worden teruggestort.';
        }

        return Util::mail($this->email, 'Aanmelding ' . $event->name, $text);
    }
}