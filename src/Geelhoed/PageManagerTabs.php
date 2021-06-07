<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed;

use Cyndaron\Geelhoed\Contest\Contest;
use Cyndaron\Geelhoed\Location\Location;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\View\Template\Template;

final class PageManagerTabs
{
    public static function locationsTab(): string
    {
        $locations = Location::fetchAll();
        $ret = (new Template())->render('Geelhoed/Location/PageManagerTab', ['locations' => $locations]);
        return $ret;
    }

    public static function membersTab(): string
    {
        $members = Member::fetchAll();
        return (new Template())->render('Geelhoed/Member/PageManagerTab', ['members' => $members]);
    }

    public static function contestsTab(): string
    {
        $contests = Contest::fetchAll([], [], 'ORDER BY registrationDeadline DESC');
        return (new Template())->render('Geelhoed/Contest/PageManagerTab', ['contests' => $contests]);
    }
}
