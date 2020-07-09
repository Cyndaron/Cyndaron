<?php
namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Page;
use Cyndaron\User\User;

use function Safe\sprintf;

final class ContestViewPage extends Page
{
    public function __construct(Contest $contest)
    {
        $user = User::getLoggedIn();
        $loggedInMember = Member::loadFromLoggedInUser();
        $mayViewOtherContestants = ($loggedInMember !== null && $loggedInMember->isContestant) || ($user !== null && $user->hasRight(Contest::RIGHT_MANAGE));
        parent::__construct(sprintf('Wedstrijd: %s', $contest->name));
        $this->addTemplateVars(['contest' => $contest, 'loggedInMember' => $loggedInMember, 'mayViewOtherContestants' => $mayViewOtherContestants]);
    }
}
