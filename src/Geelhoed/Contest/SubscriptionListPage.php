<?php
namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Page\Page;

final class SubscriptionListPage extends Page
{
    protected string $extraBodyClasses = 'geelhoed-subscription-list-page';

    public function __construct(Contest $contest)
    {
        parent::__construct('Overzicht inschrijvingen ' . $contest->name);
        $this->addScript('/src/Geelhoed/Contest/js/SubscriptionListPage.js');
        $this->addTemplateVars(['contest' => $contest]);
    }
}
