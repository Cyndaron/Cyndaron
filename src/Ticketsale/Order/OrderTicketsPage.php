<?php
namespace Cyndaron\Ticketsale\Order;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Ticketsale\Concert;
use Cyndaron\Util\Setting;
use Cyndaron\View\Page;

final class OrderTicketsPage extends Page
{
    public function __construct(int $concertId)
    {
        $this->addScript('/src/Ticketsale/Order/js/OrderTicketsPage.js');

        $concert = new Concert($concertId);
        $concert->load();
        $ticketTypes = DBConnection::doQueryAndFetchAll('SELECT * FROM ticketsale_tickettypes WHERE concertId=? ORDER BY price DESC', [$concert->id]);

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
