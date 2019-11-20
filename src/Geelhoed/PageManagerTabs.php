<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed;

use Cyndaron\Template\Template;
use Cyndaron\User\User;
use Cyndaron\Util;
use Cyndaron\Widget\Button;
use Cyndaron\Widget\Toolbar;

class PageManagerTabs
{
    public static function locationsTab(): void
    {
        echo new Toolbar('', '', (string)new Button('new', '/editor/location', 'Nieuwe locatie', 'Nieuwe locatie'));

        $locations = Location::fetchAll();
        echo (new Template())->render('Geelhoed/PageManagerTabLocation', compact('locations'));
    }

    public static function membersTab(): void
    {
        $members = Member::fetchAll([], [], '');
        echo (new Template())->render('Geelhoed/PageManagerTabMembers', compact('members'));
    }
}