<?php
namespace Cyndaron\Ticketsale;

use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\User\User;
use Cyndaron\Widget\Toolbar;

class ConcertOrderOverviewPage extends Page
{
    public function __construct(int $concertId)
    {
        $ticketTypesByOrder = [];

        $concert = new Concert($concertId);
        $concert->load();

        $ticketTypesQuery = "SELECT * FROM `ticketsale_tickettypes` WHERE concertId=? ORDER BY price DESC";
        $ticketTypes = DBConnection::doQueryAndFetchAll($ticketTypesQuery, [$concertId]);

        $ordersQuery = "    SELECT DISTINCT b.id AS bestellingsnr,lastName,initials,`email`,street,houseNumber,houseNumberAddition,postcode,city,delivery,isDelivered,hasReservedSeats,isPaid,comments,deliveryByMember,deliveryMemberName,addressIsAbroad
                    FROM     `ticketsale_orders` AS b,
                            `ticketsale_orders_tickettypes` AS bk,
                            `ticketsale_tickettypes` AS k
                    WHERE b.id=bk.orderId AND k.id=bk.tickettypeId AND k.concertId=?
                    ORDER BY bestellingsnr;";
        $orders = DBConnection::doQueryAndFetchAll($ordersQuery, [$concertId]);

        $boughtTicketTypesQuery = "SELECT orderId,tickettypeId,amount
                    FROM     `ticketsale_orders_tickettypes`";
        $boughtTicketTypes = DBConnection::doQueryAndFetchAll($boughtTicketTypesQuery, [$concertId]);

        $this->extraScripts[] = '/src/Ticketsale/ConcertOrderOverviewPage.js';

        parent::__construct('Overzicht bestellingen: ' . $concert->name);
        $this->showPrePage();

        foreach ($boughtTicketTypes as $boughtTicketType)
        {
            $orderId = $boughtTicketType['orderId'];
            $ticketTypeId = $boughtTicketType['tickettypeId'];
            if (!array_key_exists($orderId, $ticketTypesByOrder))
            {
                $ticketTypesByOrder[$orderId] = [];
            }

            $ticketTypesByOrder[$orderId][$ticketTypeId] = $boughtTicketType['amount'];
        }
        echo new Toolbar(
                '<a class="btn btn-outline-cyndaron" href="/pagemanager/concert">&laquo; Terug naar overzicht concerten</a>',
                '',
                '<a class="btn btn-outline-cyndaron" href="/concert/viewReservedSeats/' . $concertId . '">Overzicht gereserveerde plaatsen</a>'
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
                echo '<th class="rotate"><div><span>' . $ticketTypeId['name'] . '</span></div></th>';
            }
            ?>
            <th class="rotate">
                <div><span>Totaal</span></div>
            </th>
            <?php if (!$concert->forcedDelivery): ?>
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
            $verzendkosten = $bestelling['delivery'] * $concert->deliveryCost;
            $toeslag_gereserveerde_plaats = $bestelling['hasReservedSeats'] * $concert->reservedSeatCharge;

            echo '<tr><td>' . $orderId . '</td><td>' . $bestelling['lastName'] . '</td><td>' . $bestelling['initials'] . '</td><td>' . $bestelling['email'] . '</td>';
            echo '<td>' . $bestelling['street'] . '<br />' . $bestelling['postcode'] . '<br />' . $bestelling['city'] . '</td>';
            echo '<td>' . $bestelling['comments'] . '</td>';
            foreach ($ticketTypes as $ticketTypeId)
            {
                echo '<td>';
                if (array_key_exists($ticketTypeId['id'], $ticketTypesByOrder[$bestelling['bestellingsnr']]))
                {
                    printf('<b>%d</b>', $ticketTypesByOrder[$bestelling['bestellingsnr']][$ticketTypeId['id']]);
                    $totaalbedrag += $ticketTypesByOrder[$orderId][$ticketTypeId['id']] * $ticketTypeId['price'];
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

            if (!$concert->forcedDelivery)
            {
                echo '<td>' . Util::boolToText($bestelling['delivery']) . '</td>';
            }
            else
            {
                echo '<td>';
                if ($bestelling['deliveryByMember'])
                {
                    echo $bestelling['deliveryMemberName'];
                }
                else
                {
                    echo 'Nee';
                }
                echo '</td>';
            }

            echo '<td>';
            if ($bestelling['delivery'] || $concert->forcedDelivery)
            {
                echo Util::boolToText($bestelling['isDelivered']);
            }
            else
            {
                echo '&nbsp;';
            }

            echo '</td><td>' . Util::boolToText($bestelling['hasReservedSeats']);

            $extralinks = '<div class="btn-group btn-group-sm">';
            if (!$bestelling['isPaid'])
            {
                $extralinks .= '<button data-order-id="' . $orderId . '" data-csrf-token-set-is-paid="' . User::getCSRFToken('concert-order', 'setIsPaid') . '" title="Markeren als betaald" class="com-order-set-paid btn btn-sm btn-success"><span class="glyphicon glyphicon-eur"></span></button>';
            }

            if (($concert->forcedDelivery || $bestelling['delivery']) && !$bestelling['isDelivered'])
            {
                $extralinks .= '<button data-order-id="' . $orderId . '" data-csrf-token-set-is-sent="' . User::getCSRFToken('concert-order', 'setIsSent') . '" title="Markeren als verstuurd" class="com-order-set-sent btn btn-sm btn-success"><span class="glyphicon glyphicon-envelope"></span></button>';
            }

            $extralinks .= '<button data-order-id="' . $orderId . '" data-csrf-token-delete="' . User::getCSRFToken('concert-order', 'delete') . '" title="Bestelling verwijderen" class="com-order-delete btn btn-sm btn-danger"><span class="glyphicon glyphicon-trash"></span></button>';
            $extralinks .= '</div>';

            echo '</td><td>' . Util::boolToText($bestelling['isPaid']) . '</td><td>' . $extralinks . '</td></tr>';
        }

        echo '</table>';
        $this->showPostPage();
    }
}