<?php
namespace Cyndaron\Ticketsale;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Page;

final class OrderTicketsPage extends Page
{
    public function __construct(int $concertId)
    {
        $this->addScript('/src/Ticketsale/OrderTicketsPage.js');

        $concert = new Concert($concertId);
        $concert->load();
        $ticketTypes = DBConnection::doQueryAndFetchAll('SELECT * FROM ticketsale_tickettypes WHERE concertId=? ORDER BY price DESC', [$concert->id]);

        $this->templateVars['concert'] = $concert;

        parent::__construct('Kaarten bestellen: ' . $concert->name);

        $this->addTemplateVars([
            'concert' => $concert,
            'ticketTypes' => $ticketTypes,
        ]);
    }
}
