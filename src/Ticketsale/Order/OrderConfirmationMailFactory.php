<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Order;

use Cyndaron\Mail\Mail;
use Cyndaron\Request\UrlInfo;
use Cyndaron\Ticketsale\Concert\Concert;
use Cyndaron\Ticketsale\Concert\TicketDelivery;
use Cyndaron\Ticketsale\TicketType\TicketType;
use Cyndaron\Util\BuiltinSetting;
use Cyndaron\Util\Mail as UtilMail;
use Cyndaron\Util\Setting;
use Cyndaron\View\Template\ViewHelpers;
use Symfony\Component\Mime\Address;
use function array_key_exists;

final class OrderConfirmationMailFactory
{
    public function __construct(private readonly UrlInfo $urlInfo)
    {
    }

    /**
     * @param OrderTicketTypes[] $orderTicketTypes
     * @return array<int, int>
     */
    private function getOrderTicketTypeStats(array $orderTicketTypes): array
    {
        $orderTicketTypeStats = [];
        foreach ($orderTicketTypes as $orderTicketType)
        {
            $key = $orderTicketType->tickettypeId;
            if (!array_key_exists($key, $orderTicketTypeStats))
            {
                $orderTicketTypeStats[$key] = 0;
            }

            $orderTicketTypeStats[$key]++;
        }

        return $orderTicketTypeStats;
    }

    private function getDeliveryText(Order $order, Concert $concert): string
    {
        if ($concert->getDelivery() === TicketDelivery::DIGITAL)
        {
            $deliveryText = 'per e-mail aan u opgestuurd worden';
        }
        elseif ($order->delivery || ($concert->forcedDelivery && !$order->deliveryByMember))
        {
            $deliveryText = 'naar uw adres verstuurd worden';
        }
        elseif ($concert->forcedDelivery && $order->deliveryByMember)
        {
            $deliveryText = 'worden meegegeven aan ' . $order->deliveryMemberName;
        }
        else
        {
            $deliveryText = 'voor u klaargelegd worden bij de ingang van de kerk';
        }

        return $deliveryText;
    }

    public function getPaymentText(Order $order, TicketDelivery $deliveryType, float $total): string
    {
        if ($deliveryType === TicketDelivery::DIGITAL)
        {
            $url = $order->getPaymentLink($this->urlInfo->schemeAndHost);
            return "

U kunt betalen via deze link: {$url}

";
        }
        else
        {
            return '

Gebruik bij het betalen de volgende gegevens:
   Rekeningnummer: NL06INGB0000545925 t.n.v. Vlissingse Oratorium Vereniging
   Bedrag: ' . ViewHelpers::formatEuro($total) . '
   Onder vermelding van: bestelnummer ' . $order->id . '

';
        }
    }

    public function getReservedSeatsText(OrderReserveSeats $reserveSeats): string
    {
        /*if ($reserveSeats === OrderReserveSeats::RESERVE)
        {
            $numSeats = count($reservedSeats);
            return PHP_EOL . PHP_EOL . "Er zijn {$numSeats} plaatsen voor u gereserveerd.";
        }
        else*/if ($reserveSeats === OrderReserveSeats::FAILED_RESERVE)
        {
            return PHP_EOL . PHP_EOL . 'Er waren helaas niet voldoende plaatsen op Rang 1. De gerekende toeslag voor is weer van het totaalbedrag afgetrokken.';
        }

        return '';
    }

    public function getPersonalInformation(Order $order): string
    {
        $text = 'Achternaam: ' . $order->lastName . '
Voorletters: ' . $order->initials . PHP_EOL . PHP_EOL;

        $extraFields = [
            'Straatnaam en huisnummer' => $order->street,
            'Postcode' => $order->postcode,
            'Woonplaats' => $order->city,
            'Opmerkingen' => $order->comments,
        ];

        foreach ($extraFields as $description => $contents)
        {
            if (!empty($contents))
            {
                $text .= $description . ': ' . $contents . PHP_EOL;
            }
        }

        return $text;
    }

    /**
     * @param Order $order
     * @param Concert $concert
     * @param OrderReserveSeats $reserveSeats
     * @param float $total
     * @param TicketType[] $ticketTypes
     * @param OrderTicketTypes[] $orderTicketTypes
     * @return Mail
     */
    public function create(Order $order, Concert $concert, OrderReserveSeats $reserveSeats, float $total, array $ticketTypes, array $orderTicketTypes): Mail
    {
        $orderTicketTypeStats = $this->getOrderTicketTypeStats($orderTicketTypes);

        $organisation = Setting::get(BuiltinSetting::ORGANISATION);
        $deliveryText = $this->getDeliveryText($order, $concert);
        $reservedSeatsText = $this->getReservedSeatsText($reserveSeats);
        $text = 'Hartelijk dank voor uw bestelling bij ' . $organisation . '.
Na betaling zullen uw kaarten ' . $deliveryText . '.' . $reservedSeatsText;

        $deliveryType = $concert->getDelivery();
        $text .= $this->getPaymentText($order, $deliveryType, $total);

        $text .= '
Hieronder volgt een overzicht van uw bestelling.

Bestelnummer: ' . $order->id . '

Kaartsoorten:
';
        foreach ($ticketTypes as $ticketType)
        {
            $numTicketsOfType = $orderTicketTypeStats[$ticketType->id] ?? 0;
            if ($numTicketsOfType > 0)
            {
                $text .= '   ' . $ticketType->name . ': ' . $numTicketsOfType . ' à ' . ViewHelpers::formatEuro($ticketType->price) . PHP_EOL;
            }
        }
        if ($deliveryType === TicketDelivery::COLLECT_OR_DELIVER)
        {
            $text .= PHP_EOL . 'Kaarten bezorgen: ' . ViewHelpers::boolToText($order->delivery);
        }

        if ($concert->hasReservedSeats)
        {
            $text .= PHP_EOL . 'Rang: ' . ($reserveSeats === OrderReserveSeats::RESERVE ? '1' : '2') . PHP_EOL;
        }

        $text .= 'Totaalbedrag: ' . ViewHelpers::formatEuro($total) . PHP_EOL . PHP_EOL;
        $text .= $this->getPersonalInformation($order);

        $mail = UtilMail::createMailWithDefaults(
            $this->urlInfo->domain,
            new Address($order->email),
            'Bestelling concertkaarten',
            $text
        );
        return $mail;
    }
}
