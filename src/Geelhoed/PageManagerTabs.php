<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed;

use Cyndaron\Geelhoed\Contest\Contest;
use Cyndaron\Geelhoed\Sport\Sport;
use Cyndaron\Geelhoed\Tryout\Tryout;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\View\Template\TemplateRenderer;

final class PageManagerTabs
{
    public static function membersTab(TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler): string
    {
        return $templateRenderer->render('Geelhoed/Member/PageManagerTab', [
            'locations' => \Cyndaron\Location\Location::fetchAll(afterWhere: 'ORDER BY city, street'),
            'tokenDelete' => $tokenHandler->get('member', 'delete'),
            'tokenSave' => $tokenHandler->get('member', 'save'),
            'tokenRemoveGraduation' => $tokenHandler->get('member', 'removeGraduation'),
        ]);
    }

    public static function contestsTab(TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler): string
    {
        $contests = Contest::fetchAll([], [], 'ORDER BY registrationDeadline DESC');
        return $templateRenderer->render('Geelhoed/Contest/PageManagerTab', [
            'contests' => $contests,
            'tokenEdit' => $tokenHandler->get('contest', 'edit'),
            'tokenDelete' => $tokenHandler->get('contest', 'delete'),
        ]);
    }

    public static function sportsTab(TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler): string
    {
        $sports = Sport::fetchAll();
        return $templateRenderer->render('Geelhoed/Sport/PageManagerTab', [
            'sports' => $sports,
            'tokenEdit' => $tokenHandler->get('sport', 'edit'),
        ]);
    }

    public static function tryoutTab(TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler): string
    {
        $csrfTokenCreatePhotoalbums = $tokenHandler->get('tryout', 'create-photoalbums');
        $tryouts = Tryout::fetchAll();
        return $templateRenderer->render('Geelhoed/Tryout/PageManagerTab', [
            'tryouts' => $tryouts,
            'csrfTokenCreatePhotoalbums' => $csrfTokenCreatePhotoalbums,
        ]);
    }
}
