<?php
namespace Cyndaron\Ticketsale\Order;

use Cyndaron\Location\Location;
use Cyndaron\Page\Page;
use Cyndaron\Ticketsale\Concert\Concert;
use Cyndaron\Ticketsale\TicketType\TicketType;
use Cyndaron\Ticketsale\TicketType\TicketTypeRepository;
use Cyndaron\Util\BuiltinSetting;
use Cyndaron\Util\Setting;
use function file_exists;
use function strtoupper;

final class OrderTicketsPage extends Page
{
    public function __construct(Concert $concert, TicketTypeRepository $ticketTypeRepository)
    {
        $this->addScript('/src/Ticketsale/Order/js/OrderTicketsPage.js?r=2');

        $shortCode = strtoupper(Setting::get(BuiltinSetting::SHORT_CODE));
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

        $ticketTypes = $ticketTypeRepository->fetchByConcertAndSortByPrice($concert);

        $this->templateVars['concert'] = $concert;

        $this->title = 'Kaarten bestellen: ' . $concert->name;

        $this->addCss('/src/Ticketsale/css/Ticketsale.min.css');
        $this->addTemplateVars([
            'organisation' => Setting::get(BuiltinSetting::ORGANISATION),
            'concert' => $concert,
            'ticketTypes' => $ticketTypes,
        ]);
    }
}
