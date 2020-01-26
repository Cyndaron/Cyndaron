<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed;

use Cyndaron\Geelhoed\Location\Location;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Template\Template;
use Cyndaron\Widget\Button;
use Cyndaron\Widget\Toolbar;

class PageManagerTabs
{
    public static function locationsTab(): string
    {
        $ret = new Toolbar('', '', (string)new Button('new', '/editor/location', 'Nieuwe locatie', 'Nieuwe locatie'));

        $locations = Location::fetchAll();
        $ret .= (new Template())->render('Geelhoed/Location/PageManagerTab', compact('locations'));
        return $ret;
    }

    public static function membersTab(): string
    {
        $members = Member::fetchAll([], [], '');
        return (new Template())->render('Geelhoed/Member/PageManagerTab', compact('members'));
    }
}