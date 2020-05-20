<?php
namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Page;

class SubscriptionListPage extends Page
{
    public function __construct(Contest $contest)
    {
        parent::__construct('Overzicht inschrijvingen ' . $contest->name);
        $this->addScript('/src/Geelhoed/Contest/js/SubscriptionListPage.js');
        $this->addTemplateVars(compact('contest'));
    }
}
