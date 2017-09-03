<?php
namespace Cyndaron\Kaartverkoop;

use Cyndaron\DBConnection;
use Cyndaron\Request;

require_once __DIR__ . '/../../check.php';

class BestellingUpdate
{
    public function __construct()
    {
        $referrer = Request::geefReferrerVeilig();
        $actie = Request::geefGetVeilig('actie');
        $bestellings_id = Request::geefGetVeilig('bestellings_id');

        $connectie = DBConnection::getPdo();
        $prep = $connectie->prepare('SELECT * FROM kaartverkoop_bestellingen WHERE id=?');
        $prep->execute([$bestellings_id]);
        $record = $prep->fetch();
        $bezorgen_verplicht = DBConnection::geefEen('SELECT bezorgen_verplicht FROM kaartverkoop_concerten WHERE id=?', [$record['concert_id']]);

        if ($actie == 'isbetaald')
        {
            $tekst = "Hartelijk dank voor uw bestelling bij de Vlissingse Oratorium Vereniging. Wij hebben uw betaling in goede orde ontvangen.\n";
            if ($record['thuisbezorgen'] || ($bezorgen_verplicht && $record['ophalen_door_koorlid'] == 0))
            {
                $tekst .= 'Uw kaarten zullen zo spoedig mogelijk worden opgestuurd.';
            }
            elseif ($bezorgen_verplicht && $record['ophalen_door_koorlid'] == 1)
            {
                $tekst .= 'Uw kaarten zullen worden meegegeven aan ' . $record['naam_koorlid'] . '.';
            }
            else
            {
                $tekst .= 'Uw kaarten zullen op de avond van het concert voor u klaarliggen bij de kassa.';
            }

            $extraheaders = 'From: "Vlissingse Oratorium Vereniging" <noreply@vlissingse-oratoriumvereniging.nl>;
	Content-Type: text/plain; charset="UTF-8";';
            mail($record['e-mailadres'], 'Betalingsbevestiging', $tekst, $extraheaders);


            DBConnection::maakEen('UPDATE kaartverkoop_bestellingen SET `is_betaald`=1 WHERE id=?', [$bestellings_id]);
        }
        elseif ($actie == 'isbezorgd')
        {
            DBConnection::maakEen('UPDATE kaartverkoop_bestellingen SET `is_bezorgd`=1 WHERE id=?', [$bestellings_id]);
        }

        header('Location: ' . $referrer);
    }
}