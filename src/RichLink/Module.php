<?php
namespace Cyndaron\RichLink;

use Cyndaron\Category\CategoryRepository;
use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;
use Cyndaron\Module\UrlProvider;
use Cyndaron\Url\Url;
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
                class: RichLink::class,
                modelToUrl: function(RichLink $richLink)
                { return new Url($richLink->url); },
            ),
        ];
    }

    public function nameFromUrl(GenericRepository $genericRepository, array $linkParts): string|null
    {
        $richLink = $genericRepository->fetchById(RichLink::class, (int)$linkParts[1]);
        return $richLink->name ?? null;
    }

    public static function pageManagerTab(TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler, RichLinkRepository $richLinkRepository, CategoryRepository $categoryRepository): string
    {
        $templateVars = [
            'richlinks' => $richLinkRepository->fetchAllAndSortByName(),
            'categories' => $categoryRepository->fetchAllAndSortByName(),
            'tokenEdit' => $tokenHandler->get('richlink', 'edit'),
            'tokenDelete' => $tokenHandler->get('richlink', 'delete'),
            'richLinkRepository' => $richLinkRepository,
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
