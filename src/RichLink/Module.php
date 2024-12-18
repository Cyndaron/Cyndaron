<?php
namespace Cyndaron\RichLink;

use Cyndaron\Category\Category;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;
use Cyndaron\Module\UrlProvider;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\View\Template\TemplateRenderer;

final class Module implements Datatypes, UrlProvider, Routes
{
    /**
     * @inheritDoc
     */
    public function dataTypes(): array
    {
        return [
            'richlink' => new Datatype(
                singular: 'Speciale link',
                plural: 'Speciale links',
                pageManagerTab: self::pageManagerTab(...),
                pageManagerJS: '/src/RichLink/js/PageManagerTab.js',
            ),
        ];
    }

    public function url(array $linkParts): string|null
    {
        $richLink = RichLink::fetchById((int)$linkParts[1]);
        return $richLink->name ?? null;
    }

    public static function pageManagerTab(TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler): string
    {
        $templateVars = [
            'richlinks' => RichLink::fetchAllAndSortByName(),
            'categories' => Category::fetchAllAndSortByName(),
            'tokenEdit' => $tokenHandler->get('richlink', 'edit'),
            'tokenDelete' => $tokenHandler->get('richlink', 'delete'),
        ];
        return $templateRenderer->render('RichLink/PageManagerTab', $templateVars);
    }

    public function routes(): array
    {
        return [
            'richlink' => RichLinkController::class,
        ];
    }
}
