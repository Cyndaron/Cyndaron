<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale;

use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;
use Symfony\Component\HttpFoundation\Request;

final class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    protected function prepare(RequestParameters $post, Request $request): void
    {
        $concert = new Concert($this->id);
        $concert->loadIfIdIsSet();
        $concert->name = $post->getHTML('titel');
        $concert->description = $this->parseTextForInlineImages($post->getHTML('artikel'));
        $concert->descriptionWhenClosed = $this->parseTextForInlineImages($post->getHTML('descriptionWhenClosed'));
        $concert->openForSales = $post->getBool('openForSales');
        $concert->deliveryCost = $post->getFloat('deliveryCost');
        $concert->hasReservedSeats = $post->getBool('hasReservedSeats');
        $concert->reservedSeatCharge = $post->getFloat('reservedSeatCharge');
        $concert->reservedSeatsAreSoldOut = $post->getBool('reservedSeatsAreSoldOut');
        $concert->numFreeSeats = $post->getInt('numFreeSeats');
        $concert->numReservedSeats = $post->getInt('numReservedSeats');
        $concert->deliveryCostInterface = $post->getSimpleString('deliveryCostInterface');
        $concert->date = $post->getSimpleString('date');
        $concert->location = $post->getSimpleString('location');
        $concert->ticketInfo = $post->getHTML('ticketInfo');

        $delivery = $post->getInt('delivery');
        if ($delivery === TicketDelivery::COLLECT_OR_DELIVER)
        {
            $concert->forcedDelivery = false;
            $concert->digitalDelivery = false;
        }
        elseif ($delivery === TicketDelivery::FORCED_PHYSICAL)
        {
            $concert->forcedDelivery = true;
            $concert->digitalDelivery = false;
        }
        elseif ($delivery === TicketDelivery::DIGITAL)
        {
            $concert->forcedDelivery = false;
            $concert->digitalDelivery = true;
        }

        if ($concert->secretCode === '')
        {
            $concert->secretCode = Util::generateSecretCode();
        }

        if ($concert->save())
        {
            User::addNotification('Concert opgeslagen.');
        }
        else
        {
            User::addNotification('Fout bij opslaan concert');
        }

        $this->returnUrl = '/concert/order/' . $concert->id;
    }
}
