<?php
require_once('check.php');
require_once('functies.db.php');
require_once('functies.kaartverkoop.php');
require_once('pagina.php');

$connectie = newPDO();
$concert_id = geefGetVeilig('id') ?: geefEen('SELECT MAX(id) FROM kaartverkoop_concerten');

$kaartverkoop_per_bestelling = array();

$bestellingsquery = "	SELECT DISTINCT b.id AS bestellingsnr,achternaam,voorletters,`e-mailadres`,straat_en_huisnummer,postcode,woonplaats,thuisbezorgen,is_bezorgd,gereserveerde_plaatsen,is_betaald,opmerkingen,ophalen_door_koorlid,naam_koorlid,woont_in_buitenland
					FROM 	`kaartverkoop_bestellingen` AS b,
							`kaartverkoop_bestellingen_kaartsoorten` AS bk,
							`kaartverkoop_kaartsoorten` AS k
					WHERE b.id=bk.bestelling_id AND k.id=bk.kaartsoort_id AND k.concert_id=?
					ORDER BY bestellingsnr;";

$kaartverkoopquery = "SELECT bestelling_id,kaartsoort_id,aantal
					FROM 	`kaartverkoop_bestellingen_kaartsoorten`";

$kaartsoortenquery = "SELECT * FROM `kaartverkoop_kaartsoorten` WHERE concert_id=? ORDER BY prijs DESC";

$concertquery = "SELECT * FROM `kaartverkoop_concerten` WHERE id=?";

$prep = $connectie->prepare($bestellingsquery);
$prep->execute(array($concert_id));
$bestellingen = $prep->fetchAll();

$prep = $connectie->prepare($kaartverkoopquery);
$prep->execute(array($concert_id));
$kaartverkoop = $prep->fetchAll();

$prep = $connectie->prepare($kaartsoortenquery);
$prep->execute(array($concert_id));
$kaartsoorten = $prep->fetchAll();

$prep = $connectie->prepare($concertquery);
$prep->execute(array($concert_id));
$concert = $prep->fetch();

$pagina = new Pagina('Overzicht bestellingen: ' . $concert['naam']);
$pagina->toonPrePagina();

foreach ($kaartverkoop as $kaarttype)
{
    $bestellingsid = $kaarttype['bestelling_id'];
    $kaartsoort = $kaarttype['kaartsoort_id'];
    if (!array_key_exists($bestellingsid, $kaartverkoop_per_bestelling))
        $kaartverkoop_per_bestelling[$bestellingsid] = [];

    $kaartverkoop_per_bestelling[$bestellingsid][$kaartsoort] = $kaarttype['aantal'];
}
?>
<a href="gereserveerde_plaatsen.php?id=<?= $concert_id; ?>">Overzicht gereserveerde plaatsen</a>

<table class="overzichtBestellingen table table-striped">
    <tr class="rotate">
        <th class="rotate">
            <div><span>Bestellingsnummer</span></div>
        </th>
        <th class="rotate">
            <div><span>Achternaam</span></div>
        </th>
        <th class="rotate">
            <div><span>Voorletters</span></div>
        </th>
        <th class="rotate">
            <div><span>E-mailadres</span></div>
        </th>
        <th class="rotate">
            <div><span>Adres</span></div>
        </th>
        <th class="rotate">
            <div><span>Opmerkingen</span></div>
        </th>
        <?php
        foreach ($kaartsoorten as $kaartsoort)
        {
            echo '<th class="rotate"><div><span>' . $kaartsoort['naam'] . '</span></div></th>';
        }
        ?>
        <th class="rotate">
            <div><span>Totaal</span></div>
        </th>
        <?php if (!$concert['bezorgen_verplicht']): ?>
            <th class="rotate">
                <div><span>Thuisbezorgen</span></div>
            </th>
        <?php else: ?>
            <th class="rotate">
                <div><span>Meegeven aan koorlid</span></div>
            </th>
        <?php endif; ?>
        <th class="rotate">
            <div><span>Al verstuurd?</span></div>
        </th>
        <th class="rotate">
            <div><span>Geres. plaats?</span></div>
        </th>
        <th class="rotate">
            <div><span>Is betaald?</span></div>
        </th>
        <th></th>
        <th></th>
    </tr>
    <?php
    foreach ($bestellingen as $bestelling)
    {
        $totaalbedrag = 0.0;
        $verzendkosten = $bestelling['thuisbezorgen'] * $concert['verzendkosten'];
        $toeslag_gereserveerde_plaats = $bestelling['gereserveerde_plaatsen'] * $concert['toeslag_gereserveerde_plaats'];
        //$class = $bestelling['woont_in_buitenland'] ? 'buitenland' : ($bestelling['ophalen_door_);

        echo '<tr><td>' . $bestelling['bestellingsnr'] . '</td><td>' . $bestelling['achternaam'] . '</td><td>' . $bestelling['voorletters'] . '</td><td>' . $bestelling['e-mailadres'] . '</td>';
        echo '<td>' . $bestelling['straat_en_huisnummer'] . '<br />' . $bestelling['postcode'] . '<br />' . $bestelling['woonplaats'] . '</td>';
        echo '<td>' . $bestelling['opmerkingen'] . '</td>';
        foreach ($kaartsoorten as $kaartsoort)
        {
            echo '<td>';
            if (array_key_exists($kaartsoort['id'], $kaartverkoop_per_bestelling[$bestelling['bestellingsnr']]))
            {
                printf('<b>%d</b>', $kaartverkoop_per_bestelling[$bestelling['bestellingsnr']][$kaartsoort['id']]);
                $totaalbedrag += $kaartverkoop_per_bestelling[$bestelling['bestellingsnr']][$kaartsoort['id']] * $kaartsoort['prijs'];
                $totaalbedrag += $kaartverkoop_per_bestelling[$bestelling['bestellingsnr']][$kaartsoort['id']] * $verzendkosten;
                $totaalbedrag += $kaartverkoop_per_bestelling[$bestelling['bestellingsnr']][$kaartsoort['id']] * $toeslag_gereserveerde_plaats;
            }
            else
            {
                echo '&nbsp;';
            }

            echo '</td>';
        }

        echo '<td>' . naarEuro($totaalbedrag) . '</td>';

        if (!$concert['bezorgen_verplicht'])
        {
            echo '<td>' . boolNaarTekst($bestelling['thuisbezorgen']) . '</td>';
        }
        else
        {
            echo '<td>';
            if ($bestelling['ophalen_door_koorlid'])
                echo $bestelling['naam_koorlid'];
            else
                echo 'Nee';
            echo '</td>';
        }

        echo '<td>';
        if ($bestelling['thuisbezorgen'] || $concert['bezorgen_verplicht'])
            echo boolNaarTekst($bestelling['is_bezorgd']);
        else
            echo '&nbsp;';

        echo '</td><td>' . boolNaarTekst($bestelling['gereserveerde_plaatsen']);

        $extralinks = "";
        if (!$bestelling['is_betaald'])
        {
            $extralinks .= '<td><a href="bestelling_update.php?bestellings_id=' . $bestelling['bestellingsnr'] . '&amp;actie=isbetaald">Markeren als betaald</a></td>';
        }
        else
        {
            $extralinks .= '<td></td>';
        }

        if (($concert['bezorgen_verplicht'] || $bestelling['thuisbezorgen']) && !$bestelling['is_bezorgd'])
        {
            $extralinks .= '<td><a href="bestelling_update.php?bestellings_id=' . $bestelling['bestellingsnr'] . '&amp;actie=isbezorgd">Markeren als verstuurd</a></td>';
        }
        else
        {
            $extralinks .= '<td></td>';
        }

        echo '</td><td>' . boolNaarTekst($bestelling['is_betaald']) . '</td>' . $extralinks . '</tr>';
    }

    echo '</table>';

    $pagina->toonPostPagina();
    ?>
