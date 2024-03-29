<?php
namespace Cyndaron\Ticketsale\Order;

use Cyndaron\Page\Page;
use Cyndaron\Ticketsale\Concert;
use Cyndaron\Ticketsale\TicketType;
use Cyndaron\Util\Setting;
use function file_exists;
use function strtoupper;

final class OrderTicketsPage extends Page
{
    public function __construct(Concert $concert)
    {
        $this->addScript('/src/Ticketsale/Order/js/OrderTicketsPage.js?r=1');

        $shortCode = strtoupper(Setting::getShortCode());
        $specificTemplate = "OrderTicketsPage{$shortCode}-{$concert->id}";
        $orgTemplate = "OrderTicketsPage{$shortCode}";
        if (file_exists(__DIR__ . "/templates/$specificTemplate.blade.php"))
        {
            $this->template = "Ticketsale/Order/$specificTemplate";
        }
        elseif (file_exists(__DIR__ . "/templates/$orgTemplate.blade.php"))
        {
            $this->template = "Ticketsale/Order/$orgTemplate";
        }

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
