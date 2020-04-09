<?php
namespace Cyndaron\Geelhoed\Hour;

use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Page;
use Cyndaron\Util;

class MemberListPage extends Page
{
    public function __construct(Hour $hour)
    {
        $location = $hour->getLocation();
        $weekday = Util::getWeekday($hour->day);
        parent::__construct(sprintf("{$location->getName()} {$weekday} {$hour->getRange()}"));
        $this->render(['hour' => $hour, 'members' => Member::fetchAllByHour($hour)]);
    }
}