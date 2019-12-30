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
    public static function locationsTab(): void
    {
        echo new Toolbar('', '', (string)new Button('new', '/editor/location', 'Nieuwe locatie', 'Nieuwe locatie'));

        $locations = Location::fetchAll();
        echo (new Template())->render('Geelhoed/Location/PageManagerTab', compact('locations'));
    }

    public static function membersTab(): void
    {
        $members = Member::fetchAll([], [], '');
        echo (new Template())->render('Geelhoed/Member/PageManagerTab', compact('members'));
    }
}