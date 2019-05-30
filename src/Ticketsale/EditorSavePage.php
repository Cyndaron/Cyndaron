<?php
declare (strict_types = 1);

namespace Cyndaron\Ticketsale;

use Cyndaron\DBConnection;
use Cyndaron\Request;
use Cyndaron\User\User;

class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    protected function prepare()
    {
        $concert = new Concert($this->id);
        $concert->loadIfIdIsSet();
        $concert->name = Request::unsafePost('titel');;
        $concert->description = $this->parseTextForInlineImages(Request::unsafePost('artikel'));;
        $concert->descriptionWhenClosed = Request::unsafePost('descriptionWhenClosed');;
        $concert->openForSales = (bool)Request::post('openForSales');;
        $concert->forcedDelivery = (bool)Request::post('forcedDelivery');;
        $concert->deliveryCost = (float)str_replace(',', '.', Request::post('deliveryCost'));;
        $concert->hasReservedSeats = (bool)Request::post('hasReservedSeats');;
        $concert->reservedSeatCharge = (float)str_replace(',', '.', Request::post('reservedSeatCharge'));;
        $concert->reservedSeatsAreSoldOut = (bool)Request::post('reservedSeatsAreSoldOut');;

        if ($concert->save())
        {
            User::addNotification('Concert opgeslagen.');
        }
        else
        {
            $errorInfo = var_export(DBConnection::errorInfo(), true);
            User::addNotification('Fout bij opslaan concert: ' . $errorInfo);
        }

        $this->returnUrl = '/concert/order/' . $this->id;
    }
}