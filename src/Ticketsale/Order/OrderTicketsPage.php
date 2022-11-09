<?php
namespace Cyndaron\Ticketsale\Order;

use Cyndaron\Ticketsale\Concert;
use Cyndaron\Ticketsale\TicketType;
use Cyndaron\Util\Setting;
use Cyndaron\View\Page;

final class OrderTicketsPage extends Page
{
    public function __construct(int $concertId)
    {
        $this->addScript('/src/Ticketsale/Order/js/OrderTicketsPage.js?r=1');

        $organisation = Setting::get(Setting::ORGANISATION);
        if ($organisation === Setting::VALUE_ORGANISATION_VOV || $organisation === Setting::VALUE_ORGANISATION_ZCK)
        {
            $this->template = 'Ticketsale/OrderTicketsPageVOV';
        }

        $concert = new Concert($concertId);
        $concert->load();
        $ticketTypes = TicketType::fetchAll(['concertId = ?'], [$concert->id], 'ORDER BY price DESC');

        $this->templateVars['concert'] = $concert;

        parent::__construct('Kaarten bestellen: ' . $concert->name);

        $this->addCss('/src/Ticketsale/css/Ticketsale.min.css');
        $this->addTemplateVars([
            'organisation' => Setting::get(Setting::ORGANISATION),
            'concert' => $concert,
            'ticketTypes' => $ticketTypes,
        ]);
    }
}
