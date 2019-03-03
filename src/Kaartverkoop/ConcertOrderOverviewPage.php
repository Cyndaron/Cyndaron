<?php
namespace Cyndaron\Kaartverkoop;

use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\User\User;
use Cyndaron\Widget\Toolbar;

class ConcertOrderOverviewPage extends Page
{
    public function __construct(int $concert_id)
    {
        $ticketTypesByOrder = [];

        $concert = DBConnection::doQueryAndFetchFirstRow('SELECT * FROM `kaartverkoop_concerten` WHERE id=?', [$concert_id]);

        $ticketTypesQuery = "SELECT * FROM `kaartverkoop_kaartsoorten` WHERE concert_id=? ORDER BY prijs DESC";
        $ticketTypes = DBConnection::doQueryAndFetchAll($ticketTypesQuery, [$concert_id]);

        $ordersQuery = "    SELECT DISTINCT b.id AS bestellingsnr,achternaam,voorletters,`e-mailadres`,straat_en_huisnummer,postcode,woonplaats,thuisbezorgen,is_bezorgd,gereserveerde_plaatsen,is_betaald,opmerkingen,ophalen_door_koorlid,naam_koorlid,woont_in_buitenland
                    FROM     `kaartverkoop_bestellingen` AS b,
                            `kaartverkoop_bestellingen_kaartsoorten` AS bk,
                            `kaartverkoop_kaartsoorten` AS k
                    WHERE b.id=bk.bestelling_id AND k.id=bk.kaartsoort_id AND k.concert_id=?
                    ORDER BY bestellingsnr;";
        $orders = DBConnection::doQueryAndFetchAll($ordersQuery, [$concert_id]);

        $boughtTicketTypesQuery = "SELECT bestelling_id,kaartsoort_id,aantal
                    FROM     `kaartverkoop_bestellingen_kaartsoorten`";
        $boughtTicketTypes = DBConnection::doQueryAndFetchAll($boughtTicketTypesQuery, [$concert_id]);

        $this->extraScripts[] = '/src/Kaartverkoop/ConcertOrderOverviewPage.js';

        parent::__construct('Overzicht bestellingen: ' . $concert['naam']);
        $this->showPrePage();

        foreach ($boughtTicketTypes as $boughtTicketType)
        {
            $orderId = $boughtTicketType['bestelling_id'];
            $ticketTypeId = $boughtTicketType['kaartsoort_id'];
            if (!array_key_exists($orderId, $ticketTypesByOrder))
            {
                $ticketTypesByOrder[$orderId] = [];
            }

            $ticketTypesByOrder[$orderId][$ticketTypeId] = $boughtTicketType['aantal'];
        }
        echo new Toolbar(
                '<a class="btn btn-outline-cyndaron" href="/pagemanager/concert">&laquo; Terug naar overzicht concerten</a>',
                '',
                '<a class="btn btn-outline-cyndaron" href="/concert/viewReservedSeats/' . $concert_id . '">Overzicht gereserveerde plaatsen</a>'
            );
        ?>

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
            foreach ($ticketTypes as $ticketTypeId)
            {
                echo '<th class="rotate"><div><span>' . $ticketTypeId['naam'] . '</span></div></th>';
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
            <th style="min-width: 150px;"></th>
        </tr>
        <?php
        foreach ($orders as $bestelling)
        {
            $orderId = $bestelling['bestellingsnr'];
            $totaalbedrag = 0.0;
            $verzendkosten = $bestelling['thuisbezorgen'] * $concert['verzendkosten'];
            $toeslag_gereserveerde_plaats = $bestelling['gereserveerde_plaatsen'] * $concert['toeslag_gereserveerde_plaats'];
            //$class = $bestelling['woont_in_buitenland'] ? 'buitenland' : ($bestelling['ophalen_door_);

            echo '<tr><td>' . $orderId . '</td><td>' . $bestelling['achternaam'] . '</td><td>' . $bestelling['voorletters'] . '</td><td>' . $bestelling['e-mailadres'] . '</td>';
            echo '<td>' . $bestelling['straat_en_huisnummer'] . '<br />' . $bestelling['postcode'] . '<br />' . $bestelling['woonplaats'] . '</td>';
            echo '<td>' . $bestelling['opmerkingen'] . '</td>';
            foreach ($ticketTypes as $ticketTypeId)
            {
                echo '<td>';
                if (array_key_exists($ticketTypeId['id'], $ticketTypesByOrder[$bestelling['bestellingsnr']]))
                {
                    printf('<b>%d</b>', $ticketTypesByOrder[$bestelling['bestellingsnr']][$ticketTypeId['id']]);
                    $totaalbedrag += $ticketTypesByOrder[$orderId][$ticketTypeId['id']] * $ticketTypeId['prijs'];
                    $totaalbedrag += $ticketTypesByOrder[$orderId][$ticketTypeId['id']] * $verzendkosten;
                    $totaalbedrag += $ticketTypesByOrder[$orderId][$ticketTypeId['id']] * $toeslag_gereserveerde_plaats;
                }
                else
                {
                    echo '&nbsp;';
                }

                echo '</td>';
            }

            echo '<td>' . Util::formatEuro($totaalbedrag) . '</td>';

            if (!$concert['bezorgen_verplicht'])
            {
                echo '<td>' . Util::boolToText($bestelling['thuisbezorgen']) . '</td>';
            }
            else
            {
                echo '<td>';
                if ($bestelling['ophalen_door_koorlid'])
                {
                    echo $bestelling['naam_koorlid'];
                }
                else
                {
                    echo 'Nee';
                }
                echo '</td>';
            }

            echo '<td>';
            if ($bestelling['thuisbezorgen'] || $concert['bezorgen_verplicht'])
            {
                echo Util::boolToText($bestelling['is_bezorgd']);
            }
            else
            {
                echo '&nbsp;';
            }

            echo '</td><td>' . Util::boolToText($bestelling['gereserveerde_plaatsen']);

            $extralinks = '<div class="btn-group btn-group-sm">';
            if (!$bestelling['is_betaald'])
            {
                $extralinks .= '<button data-order-id="' . $orderId . '" data-csrf-token-set-is-paid="' . User::getCSRFToken('concert-order', 'setIsPaid') . '" title="Markeren als betaald" class="com-order-set-paid btn btn-sm btn-success"><span class="glyphicon glyphicon-eur"></span></button>';
            }

            if (($concert['bezorgen_verplicht'] || $bestelling['thuisbezorgen']) && !$bestelling['is_bezorgd'])
            {
                $extralinks .= '<button data-order-id="' . $orderId . '" data-csrf-token-set-is-sent="' . User::getCSRFToken('concert-order', 'setIsSent') . '" title="Markeren als verstuurd" class="com-order-set-sent btn btn-sm btn-success"><span class="glyphicon glyphicon-envelope"></span></button>';
            }

            $extralinks .= '<button data-order-id="' . $orderId . '" data-csrf-token-delete="' . User::getCSRFToken('concert-order', 'delete') . '" title="Bestelling verwijderen" class="com-order-delete btn btn-sm btn-danger"><span class="glyphicon glyphicon-trash"></span></button>';
            $extralinks .= '</div>';

            echo '</td><td>' . Util::boolToText($bestelling['is_betaald']) . '</td><td>' . $extralinks . '</td></tr>';
        }

        echo '</table>';
        $this->showPostPage();
    }
}