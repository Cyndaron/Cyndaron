<?php
namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\View\Page;

final class OverviewPage extends Page
{
    public function __construct()
    {
        $contests = Contest::fetchAllCurrentWithDate();
        parent::__construct('Overzicht wedstrijden');
        $this->addTemplateVars(['contests' => $contests]);
    }
}
