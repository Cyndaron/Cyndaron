<?php
namespace Cyndaron\Geelhoed\Contest\Page;

use Cyndaron\Geelhoed\Contest\Model\Contest;
use Cyndaron\Geelhoed\Contest\Model\ContestDate;
use Cyndaron\Geelhoed\Contest\Model\ContestDateRepository;
use Cyndaron\Geelhoed\Contest\Model\ContestMemberRepository;
use Cyndaron\Page\Page;

final class SubscriptionListPage extends Page
{
    public string $extraBodyClasses = 'geelhoed-subscription-list-page';

    public function __construct(Contest $contest, ContestDateRepository $contestDateRepository, ContestMemberRepository $contestMemberRepository)
    {
        $this->title = 'Overzicht inschrijvingen ' . $contest->name;
        $this->addScript('/src/Geelhoed/Contest/js/SubscriptionListPage.js');
        $this->addTemplateVars([
            'contest' => $contest,
            'contestDateRepository' => $contestDateRepository,
            'contestMemberRepository' => $contestMemberRepository,
        ]);
    }
}
