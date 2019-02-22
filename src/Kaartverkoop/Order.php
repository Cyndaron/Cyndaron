<?php
declare (strict_types = 1);

namespace Cyndaron\Kaartverkoop;

use Cyndaron\DBConnection;
use Cyndaron\Model;
use \Exception;

class Order extends Model
{
    protected static $table = 'kaartverkoop_bestellingen';

    const MAIL_HEADERS = [
        'From' => '"Vlissingse Oratorium Vereniging" <noreply@vlissingse-oratoriumvereniging.nl>',
        'Content-Type' => 'text/plain; charset="UTF-8"',
    ];

    public function setIsPaid()
    {
        if ($this->id === null || $this->record === null)
        {
            throw new Exception('ID is null!');
        }

        $forcedDelivery = DBConnection::doQueryAndFetchOne('SELECT bezorgen_verplicht FROM kaartverkoop_concerten WHERE id=?', [$this->record['concert_id']]);

        $text = "Hartelijk dank voor uw bestelling bij de Vlissingse Oratorium Vereniging. Wij hebben uw betaling in goede orde ontvangen.\n";
        if ($this->record['thuisbezorgen'] || ($forcedDelivery && $this->record['ophalen_door_koorlid'] == 0))
        {
            $text .= 'Uw kaarten zullen zo spoedig mogelijk worden opgestuurd.';
        }
        elseif ($forcedDelivery && $this->record['ophalen_door_koorlid'] == 1)
        {
            $text .= 'Uw kaarten zullen worden meegegeven aan ' . $this->record['naam_koorlid'] . '.';
        }
        else
        {
            $text .= 'Uw kaarten zullen op de avond van het concert voor u klaarliggen bij de kassa.';
        }

        $extraheaders = 'From: "Vlissingse Oratorium Vereniging" <noreply@vlissingse-oratoriumvereniging.nl>;' . "\n" .
            'Content-Type: text/plain; charset="UTF-8";';
        mail($this->record['e-mailadres'], 'Betalingsbevestiging', $text, $extraheaders);


        DBConnection::doQuery('UPDATE kaartverkoop_bestellingen SET `is_betaald`=1 WHERE id=?', [$this->id]);
    }

    public function setIsSent()
    {
        if ($this->id === null)
        {
            throw new Exception('id is null!');
        }

        DBConnection::doQuery('UPDATE kaartverkoop_bestellingen SET `is_bezorgd`=1 WHERE id=?', [$this->id]);
    }
}