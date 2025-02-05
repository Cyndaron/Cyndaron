<?php
namespace Cyndaron\Geelhoed\Hour;

use Cyndaron\Geelhoed\Location\LocationRepository;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\Member\MemberRepository;
use Cyndaron\Page\Page;
use Cyndaron\View\Template\ViewHelpers;
use function sprintf;

final class MemberListPage extends Page
{
    public function __construct(Hour $hour, MemberRepository $memberRepository, LocationRepository $locationRepository)
    {
        $weekday = ViewHelpers::getDutchWeekday($hour->day);
        $this->title = "{$hour->location->getName()} {$weekday} {$hour->getRange()}";
        $this->addTemplateVars([
            'hour' => $hour,
            'members' => $memberRepository->fetchAllByHour($hour),
            'locationRepository' => $locationRepository,
        ]);
    }
}
