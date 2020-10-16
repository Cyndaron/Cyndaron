<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale;

use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;

final class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    protected function prepare(RequestParameters $post): void
    {
        $concert = new Concert($this->id);
        $concert->loadIfIdIsSet();
        $concert->name = $post->getHTML('titel');
        $concert->description = $this->parseTextForInlineImages($post->getHTML('artikel'));
        $concert->descriptionWhenClosed = $this->parseTextForInlineImages($post->getHTML('descriptionWhenClosed'));
        $concert->openForSales = $post->getBool('openForSales');
        $concert->forcedDelivery = $post->getBool('forcedDelivery');
        $concert->deliveryCost = $post->getFloat('deliveryCost');
        $concert->hasReservedSeats = $post->getBool('hasReservedSeats');
        $concert->reservedSeatCharge = $post->getFloat('reservedSeatCharge');
        $concert->reservedSeatsAreSoldOut = $post->getBool('reservedSeatsAreSoldOut');
        $concert->numFreeSeats = $post->getInt('numFreeSeats');
        $concert->numReservedSeats = $post->getInt('numReservedSeats');

        if ($concert->save())
        {
            User::addNotification('Concert opgeslagen.');
        }
        else
        {
            User::addNotification('Fout bij opslaan concert');
        }

        $this->returnUrl = '/concert/order/' . $this->id;
    }
}
