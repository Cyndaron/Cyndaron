<?php
declare (strict_types = 1);

namespace Cyndaron\Registration;

use Cyndaron\Error\IncompleteData;
use Cyndaron\Model;
use Cyndaron\Setting;
use Cyndaron\Template\Template;
use Cyndaron\Template\ViewHelpers;
use \Exception;

class Registration extends Model
{
    public const TABLE = 'registration_orders';
    public const TABLE_FIELDS = ['eventId', 'lastName', 'initials', 'registrationGroup', 'vocalRange', 'birthYear', 'lunch', 'lunchType', 'bhv', 'kleinkoor', 'kleinkoorExplanation', 'participatedBefore', 'numPosters', 'email', 'street', 'houseNumber', 'houseNumberAddition', 'postcode', 'city', 'comments', 'isPaid', 'currentChoir', 'choirPreference', 'approvalStatus', 'phone', 'choirExperience', 'performedBefore'];

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
    public string $email = '';
    public string $phone = '';
    public string $street;
    public int $houseNumber;
    public string $houseNumberAddition;
    public string $postcode;
    public string $city = '';
    public string $currentChoir = '';
    public string $choirPreference = '';
    public int $choirExperience = 0;
    public bool $performedBefore = false;
    public string $comments;
    public int $approvalStatus = self::APPROVAL_UNDECIDED;
    public bool $isPaid = false;

    public static function loadByEvent(Event $event): array
    {
        return static::fetchAll(['eventId = ?'], [$event->id], 'ORDER BY id');
    }

    public function getEvent(): Event
    {
        return Event::loadFromDatabase((int)$this->eventId);
    }

    /**
     * @param float $registrationTotal
     * @param array $registrationTicketTypes
     * @return bool
     */
    public function sendConfirmationMail(float $registrationTotal, array $registrationTicketTypes): bool
    {
        $event = $this->getEvent();

        if (Setting::get('organisation') === Setting::ORGANISATION_SBK)
        {

            $text = 'Hartelijk dank voor je aanmelding op de SBK-website voor deelname als koorzanger voor ' . $event->name . '. Je aanmelding is door het SBK-bestuur in goede orde ontvangen.

Zo spoedig mogelijk na sluiting van de aanmeldingsprocedure laat het SBK-bestuur je via de mail weten of je als koorzanger kunt deelnemen in het SBK-koor. Je hoeft nu dus nog niet te betalen.';

            return Util::mail($this->email, 'Aanmelding ' . $event->name . ' ontvangen', $text);
        }

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
        if (Setting::get('organisation') === Setting::ORGANISATION_VOV)
        {
            $templateFile = 'Registration/ConfirmationMailVOV';
        }

        $template = new Template();
        $registration = $this;
        $args = compact('registration', 'event', 'registrationTotal', 'ticketTypes', 'registrationTicketTypes', 'lunchText', 'extraFields');
        $text = $template->render($templateFile, $args);
        // We're sending a plaintext mail, so avoid displaying html entities.
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        return Util::mail($this->email, 'Inschrijving ' . $event->name, $text);
    }

    public function setIsPaid(): bool
    {
        if ($this->id === null)
        {
            throw new IncompleteData('ID is null!');
        }

        $event = $this->getEvent();

        $this->isPaid = true;
        $this->save();

        $organisation = Setting::get('organisation');
        $text = "Hartelijk dank voor uw inschrijving bij $organisation. Wij hebben uw betaling in goede orde ontvangen.\n";
        if ($organisation !== Setting::ORGANISATION_VOV)
        {
            $text .= 'Eventueel bestelde kaarten voor vrienden en familie zullen op de avond van het concert voor u klaarliggen bij de kassa.';
        }
        elseif ($organisation === Setting::ORGANISATION_SBK)
        {
            $text = 'Beste koorzanger,

Je betaling is ontvangen waarmee je plaatsing op de deelnemerslijst definitief is geworden. Tot ziens op de eerste repetitie!

Met vriendelijke groet,

Stichting Bijzondere Koorprojecten';
        }

        return Util::mail($this->email, 'Betalingsbevestiging ' . $event->name, $text);
    }

    public function calculateTotal(array $registrationTicketTypes = []): float
    {
        $event = $this->getEvent();
        $registrationTotal = 0;
        if ($this->registrationGroup === 2)
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
            throw new IncompleteData('ID is null!');
        }

        $this->approvalStatus = self::APPROVAL_APPROVED;
        $this->save();

        $event = $this->getEvent();

        $text = '';
        if (Setting::get('organisation') === Setting::ORGANISATION_SBK)
        {
            $registrationTotal = $this->calculateTotal();

            $text = 'Beste koorzanger,

Nogmaals dank voor je belangstelling voor ' . $event->name . '. Inmiddels zijn alle aanmeldingen bekeken en kunnen we je met plezier melden dat je bent geplaatst op de deelnemerslijst. Die plaatsing wordt definitief na ontvangst van de bijdrage in de kosten ad ' . \Cyndaron\Template\ViewHelpers::formatEuro($registrationTotal) . '. Wij vragen je dat binnen twee weken te doen waarna je bericht krijgt van de definitieve plaatsing. 

Gebruik bij het betalen de volgende gegevens:
   Rekeningnummer: NL72 RABO 0342 0672 22 t.n.v. Bijzondere Koorprojecten
   Bedrag: ' . ViewHelpers::formatEuro($registrationTotal) . '
   Onder vermelding van: aanmeldingsnummer ' . $this->id . '

We kijken uit naar plezierige repetities en een mooi concert!

Met vriendelijke groet,

Stichting Bijzondere Koorprojecten';
        }

        return Util::mail($this->email, 'Aanmelding ' . $event->name . ' goedgekeurd', $text);
    }

    public function setDisapproved(): bool
    {
        if ($this->id === null)
        {
            throw new IncompleteData('ID is null!');
        }

        $this->approvalStatus = self::APPROVAL_DISAPPROVED;
        $this->save();

        $event = $this->getEvent();

        if (Setting::get('organisation') === Setting::ORGANISATION_SBK)
        {
            $text = 'Beste koorzanger,

Nogmaals dank voor je belangstelling voor ' . $event->name . '. Inmiddels zijn alle aanmeldingen bekeken. Bij de indeling speelt de balans in stemsoorten de belangrijkste rol. Dat betekent helaas dat we je niet hebben kunnen plaatsen.

Met vriendelijke groet,

Stichting Bijzondere Koorprojecten';

            return Util::mail($this->email, 'Aanmelding ' . $event->name, $text);
        }

        if ($event->requireApproval)
        {
            $text = '';
        }
        else
        {
            $text = 'Uw aanmelding is geannuleerd. Eventuele betalingen zullen worden teruggestort.';
        }

        return Util::mail($this->email, 'Aanmelding ' . $event->name, $text);
    }

    public function shouldPay(): bool
    {
        return !$this->isPaid && $this->approvalStatus === self::APPROVAL_APPROVED;
    }
}