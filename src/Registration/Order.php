<?php
declare (strict_types = 1);

namespace Cyndaron\Registration;

use Cyndaron\Model;
use \Exception;

class Order extends Model
{
    const TABLE = 'registration_orders';
    const TABLE_FIELDS = ['eventId', 'lastName', 'initials', 'vocalRange', 'lunch', 'email', 'street', 'postcode', 'city', 'comments', 'isPaid'];

    const MAIL_HEADERS = [
        'From' => '"Scratch Messiah Zeeland" <noreply@scratchzeeland.nl>',
        'Content-Type' => 'text/plain; charset="UTF-8"',
    ];

    public $eventId;
    public $lastName;
    public $initials;
    public $vocalRange;
    public $lunch = false;
    public $email;
    public $street;
    public $postcode;
    public $city;
    public $comments;
    public $isPaid = false;

    public function getEvent(): Event
    {
        return Event::loadFromDatabase($this->eventId);
    }

    /**
     * @param float $orderTotal
     * @param array $orderTicketTypes
     * @return bool
     */
    public function sendConfirmationMail(float $orderTotal, array $orderTicketTypes)
    {
        $event = $this->getEvent();
        $ticketTypes = EventTicketType::loadByEvent($event);
        $text = 'Hartelijk dank voor uw inschrijving bij de Scratch Messiah Zeeland.
Na betaling is uw inschrijving definitief. Eventueel bestelde kaarten voor vrienden en familie zullen op de avond van het concert voor u klaargelegd worden bij de ingang van de kerk.

Gebruik bij het betalen de volgende gegevens:
   Rekeningnummer: NLxxYYYYzzzzzzzzzz t.n.v. Scratch Messiah Zeeland
   Bedrag: ' . Util::formatEuro($orderTotal) . '
   Onder vermelding van: inschrijvingsnummer ' . $this->id . '


Hieronder volgt een overzicht van uw inschrijving.

Inschrijvingsnummer: ' . $this->id . '

Achternaam: ' . $this->lastName . '
Voorletters: ' . $this->initials . '
Stemsoort: ' . $this->vocalRange . PHP_EOL . PHP_EOL;

        $extraFields = [
            'Straatnaam en huisnummer' => $this->street,
            'Postcode' => $this->postcode,
            'Woonplaats' => $this->city,
            'Opmerkingen' => $this->comments,
        ];

        foreach ($extraFields as $description => $contents)
        {
            if (!empty($contents))
            {
                $text .= $description . ': ' . $contents . PHP_EOL;
            }
        }

        if (!empty($ticketTypes))
        {
            $text .= 'Kaartsoorten:' . PHP_EOL;
            foreach ($ticketTypes as $ticketType)
            {
                if ($orderTicketTypes[$ticketType->id] > 0)
                {
                    $text .= '   ' . $ticketType->name . ': ' . $orderTicketTypes[$ticketType->id] . ' à ' . Util::formatEuro((float)$ticketType->price) . PHP_EOL;
                }
            }
        }
        $text.= PHP_EOL . 'Totaalbedrag: ' . Util::formatEuro($orderTotal);

        return mail($this->email, 'Inschrijving ' . $event->name, $text, Order::MAIL_HEADERS);
    }

    public function setIsPaid()
    {
        if ($this->id === null)
        {
            throw new Exception('ID is null!');
        }

        $this->isPaid = true;
        $this->save();

        $text = "Hartelijk dank voor uw inschrijving bij de Scratch Messiah Zeeland. Wij hebben uw betaling in goede orde ontvangen.\n"
              . 'Eventueel bestelde kaarten voor vrienden en familie zullen op de avond van het concert voor u klaarliggen bij de kassa.';

        mail($this->email, 'Betalingsbevestiging', $text, static::MAIL_HEADERS);
    }
}