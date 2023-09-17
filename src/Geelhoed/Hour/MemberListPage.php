<?php
namespace Cyndaron\Geelhoed\Hour;

use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Page\Page;
use Cyndaron\View\Template\ViewHelpers;
use function sprintf;

final class MemberListPage extends Page
{
    public function __construct(Hour $hour)
    {
        $location = $hour->getLocation();
        $weekday = ViewHelpers::getDutchWeekday($hour->day);
        parent::__construct(sprintf("{$location->getName()} {$weekday} {$hour->getRange()}"));
        $this->addTemplateVars(['hour' => $hour, 'members' => Member::fetchAllByHour($hour)]);
    }
}
