<?php
declare (strict_types = 1);

namespace Cyndaron\Kaartverkoop;

use Cyndaron\DBConnection;
use Cyndaron\Request;
use Cyndaron\User\User;

class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    protected function prepare()
    {
        $contentTitle = Request::unsafePost('titel');
        $description = $this->parseTextForInlineImages(Request::unsafePost('artikel'));
        $closedDescription = Request::unsafePost('closedDescription');
        $salesOpen = (bool)Request::post('salesOpen');
        $forcedDelivery = (bool)Request::post('forcedDelivery');
        $hasReservedSeats = (bool)Request::post('hasReservedSeats');
        $reservedSeatsSoldOut = (bool)Request::post('reservedSeatsSoldOut');
        $deliveryFee = (float)str_replace(',', '.', Request::post('deliveryFee'));
        $reservedSeatFee = (float)str_replace(',', '.', Request::post('reservedSeatFee'));

        if ($this->id > 0)
        {
            DBConnection::doQuery('UPDATE kaartverkoop_concerten SET 
                                         naam = ?, beschrijving = ?, beschrijving_indien_gesloten = ?, 
                                         open_voor_verkoop = ? , bezorgen_verplicht = ?, heeft_gereserveerde_plaatsen = ?,
                                         gereserveerde_plaatsen_uitverkocht = ?, verzendkosten = ?, toeslag_gereserveerde_plaats = ?
                                         WHERE id = ?', [
                $contentTitle,
                $description,
                $closedDescription,
                $salesOpen,
                $forcedDelivery,
                $hasReservedSeats,
                $reservedSeatsSoldOut,
                $deliveryFee,
                $reservedSeatFee,
                $this->id
            ]);
        }
        else
        {
            $result = DBConnection::doQuery('INSERT INTO kaartverkoop_concerten(naam, beschrijving, beschrijving_indien_gesloten, 
                                         open_voor_verkoop, bezorgen_verplicht, heeft_gereserveerde_plaatsen,
                                         gereserveerde_plaatsen_uitverkocht, verzendkosten, toeslag_gereserveerde_plaats) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $contentTitle,
                $description,
                $closedDescription,
                $salesOpen,
                $forcedDelivery,
                $hasReservedSeats,
                $reservedSeatsSoldOut,
                $deliveryFee,
                $reservedSeatFee,
            ]);
            if ($result === false)
            {
                echo 'Fout bij opslaan concert.';
                var_dump(DBConnection::errorInfo());
                die();
            }
            $this->id = $result;
        }

        User::addNotification('Concert bewerkt.');
        $this->returnUrl = '/concert/order/' . $this->id;
    }
}