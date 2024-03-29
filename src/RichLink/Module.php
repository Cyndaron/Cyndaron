<?php
namespace Cyndaron\RichLink;

use Cyndaron\Category\Category;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;
use Cyndaron\Module\UrlProvider;
use Cyndaron\View\Template\TemplateRenderer;

final class Module implements Datatypes, UrlProvider, Routes
{
    /**
     * @inheritDoc
     */
    public function dataTypes(): array
    {
        return [
            'richlink' => Datatype::fromArray([
                'singular' => 'Speciale link',
                'plural' => 'Speciale links',
                'pageManagerTab' => self::class . '::pageManagerTab',
                'pageManagerJS' => '/src/RichLink/js/PageManagerTab.js',
            ]),
        ];
    }

    public function url(array $linkParts): string|null
    {
        $richLink = RichLink::fetchById((int)$linkParts[1]);
        return $richLink->name ?? null;
    }

    public static function pageManagerTab(TemplateRenderer $templateRenderer): string
    {
        $templateVars = [
            'richlinks' => RichLink::fetchAll([], [], 'ORDER BY name'),
            'categories' => Category::fetchAll([], [], 'ORDER BY name'),
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
