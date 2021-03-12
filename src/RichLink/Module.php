<?php
namespace Cyndaron\RichLink;

use Cyndaron\Category\Category;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;
use Cyndaron\Module\UrlProvider;
use Cyndaron\View\Template\Template;

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

    public function url(array $linkParts): ?string
    {
        $richLink = RichLink::loadFromDatabase((int)$linkParts[1]);
        return $richLink->name ?? null;
    }

    public static function pageManagerTab(): string
    {
        $templateVars = [
            'richlinks' => RichLink::fetchAll([], [], 'ORDER BY name'),
            'categories' => Category::fetchAll([], [], 'ORDER BY name'),
        ];
        return (new Template())->render('RichLink/PageManagerTab', $templateVars);
    }

    public function routes(): array
    {
        return [
            'richlink' => RichLinkController::class,
        ];
    }
}
