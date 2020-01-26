<?php
declare (strict_types = 1);

namespace Cyndaron\RegistrationSbk;

use Cyndaron\Model;
use \Exception;

class Registration extends Model
{
    const TABLE = 'registrationsbk_registrations';
    const TABLE_FIELDS = ['eventId', 'lastName', 'initials', 'vocalRange', 'email', 'phone', 'city', 'currentChoir', 'choirExperience', 'performedBefore', 'comments', 'approvalStatus', 'isPaid'];

    const APPROVAL_UNDECIDED = 0;
    const APPROVAL_APPROVED = 1;
    const APPROVAL_DISAPPROVED = 2;

    public $eventId;
    public $lastName;
    public $initials;
    public $vocalRange;
    public $email = '';
    public $phone = '';
    public $city = '';
    public $currentChoir = '';
    public $choirExperience = 0;
    public $performedBefore = false;
    public $comments;
    public $approvalStatus = self::APPROVAL_UNDECIDED;
    public $isPaid = false;

    public static function loadByEvent(Event $event)
    {
        return static::fetchAll(['eventId = ?'], [$event->id], 'ORDER BY id');
    }

    public function getEvent(): Event
    {
        return Event::loadFromDatabase((int)$this->eventId);
    }

    public function sendConfirmationMail()
    {
        $event = $this->getEvent();

        $text = 'Hartelijk dank voor je aanmelding op de SBK-website voor deelname als koorzanger voor ' . $event->name . '. Je aanmelding is door het SBK-bestuur in goede orde ontvangen.

Zo spoedig mogelijk na sluiting van de aanmeldingsprocedure laat het SBK-bestuur je via de mail weten of je als koorzanger kunt deelnemen in het SBK-koor. Je hoeft nu dus nog niet te betalen.';

        return Util::mail($this->email, 'Aanmelding ' . $event->name . ' ontvangen', $text);
    }

    /**
     * @return bool
     */
    public function setApproved()
    {
        if ($this->id === null)
        {
            throw new Exception('ID is null!');
        }

        $this->approvalStatus = self::APPROVAL_APPROVED;
        $this->save();

        $event = $this->getEvent();
        $orderTotal = (float)$event->registrationCost;

        $text = 'Beste koorzanger,

Nogmaals dank voor je belangstelling voor ' . $event->name . '. Inmiddels zijn alle aanmeldingen bekeken en kunnen we je met plezier melden dat je bent geplaatst op de deelnemerslijst. Die plaatsing wordt definitief na ontvangst van de bijdrage in de kosten ad ' . Util::formatEuro($orderTotal) . '. Wij vragen je dat binnen twee weken te doen waarna je bericht krijgt van de definitieve plaatsing. 

Gebruik bij het betalen de volgende gegevens:
   Rekeningnummer: NL72 RABO 0342 0672 22 t.n.v. Bijzondere Koorprojecten
   Bedrag: ' . Util::formatEuro($orderTotal) . '
   Onder vermelding van: aanmeldingsnummer ' . $this->id . '

We kijken uit naar plezierige repetities en een mooi concert!

Met vriendelijke groet,

Stichting Bijzondere Koorprojecten';

        return Util::mail($this->email, 'Aanmelding ' . $event->name . ' goedgekeurd', $text);
    }

    public function setDisapproved()
    {
        if ($this->id === null)
        {
            throw new Exception('ID is null!');
        }

        $this->approvalStatus = self::APPROVAL_DISAPPROVED;
        $this->save();

        $event = $this->getEvent();

        $text = 'Beste koorzanger,

Nogmaals dank voor je belangstelling voor ' . $event->name . '. Inmiddels zijn alle aanmeldingen bekeken. Bij de indeling speelt de balans in stemsoorten de belangrijkste rol. Dat betekent helaas dat we je niet hebben kunnen plaatsen.

Met vriendelijke groet,

Stichting Bijzondere Koorprojecten';

        return Util::mail($this->email, 'Aanmelding ' . $event->name, $text);
    }

    public function setIsPaid()
    {
        if ($this->id === null)
        {
            throw new Exception('ID is null!');
        }

        $event = $this->getEvent();

        $this->isPaid = true;
        $this->save();

        $text = 'Beste koorzanger,

Je betaling is ontvangen waarmee je plaatsing op de deelnemerslijst definitief is geworden. Tot ziens op de eerste repetitie!

Met vriendelijke groet,

Stichting Bijzondere Koorprojecten';

        return Util::mail($this->email, 'Betalingsbevestiging ' . $event->name, $text);
    }

    public function calculateTotal(): float
    {
        $event = $this->getEvent();
        return (float)$event->registrationCost;
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
                return 'Afgewezen';
        }
        return 'Onbekend';
    }

    public function shouldPay(): bool
    {
        return !$this->isPaid && $this->approvalStatus == self::APPROVAL_APPROVED;
    }
}