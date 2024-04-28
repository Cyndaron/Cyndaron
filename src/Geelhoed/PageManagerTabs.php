<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed;

use Cyndaron\Geelhoed\Contest\Contest;
use Cyndaron\Geelhoed\Location\Location;
use Cyndaron\Geelhoed\Sport\Sport;
use Cyndaron\Geelhoed\Tryout\Tryout;
use Cyndaron\User\UserSession;
use Cyndaron\View\Template\TemplateRenderer;

final class PageManagerTabs
{
    public static function locationsTab(TemplateRenderer $templateRenderer): string
    {
        $locations = Location::fetchAll();
        $ret = $templateRenderer->render('Geelhoed/Location/PageManagerTab', ['locations' => $locations]);
        return $ret;
    }

    public static function membersTab(TemplateRenderer $templateRenderer): string
    {
        return $templateRenderer->render('Geelhoed/Member/PageManagerTab', [
            'locations' => \Cyndaron\Geelhoed\Location\Location::fetchAll(afterWhere: 'ORDER BY city, street'),
        ]);
    }

    public static function contestsTab(TemplateRenderer $templateRenderer): string
    {
        $contests = Contest::fetchAll([], [], 'ORDER BY registrationDeadline DESC');
        return $templateRenderer->render('Geelhoed/Contest/PageManagerTab', ['contests' => $contests]);
    }

    public static function sportsTab(TemplateRenderer $templateRenderer): string
    {
        $sports = Sport::fetchAll();
        return $templateRenderer->render('Geelhoed/Sport/PageManagerTab', ['sports' => $sports]);
    }

    public static function tryoutTab(TemplateRenderer $templateRenderer): string
    {
        $csrfTokenCreatePhotoalbums = UserSession::getCSRFToken('tryout', 'create-photoalbums');
        $tryouts = Tryout::fetchAll();
        return $templateRenderer->render('Geelhoed/Tryout/PageManagerTab', [
            'tryouts' => $tryouts,
            'csrfTokenCreatePhotoalbums' => $csrfTokenCreatePhotoalbums,
        ]);
    }
}
