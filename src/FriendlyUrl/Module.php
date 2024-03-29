<?php
namespace Cyndaron\FriendlyUrl;

use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;
use Cyndaron\View\Template\TemplateRenderer;

final class Module implements Datatypes, Routes
{
    /**
     * @inheritDoc
     */
    public function dataTypes(): array
    {
        return [
            'friendlyurl' => Datatype::fromArray([
                'singular' => 'Friendly URL',
                'plural' => 'Friendly URL\'s',
                'pageManagerTab' => self::class . '::pageManagerTab',
                'pageManagerJS' => '/src/FriendlyUrl/js/PageManagerTab.js',
            ]),
        ];
    }

    /**
     * @inheritDoc
     */
    public function routes(): array
    {
        return [
            'friendlyurl' => FriendlyUrlController::class,
        ];
    }

    public static function pageManagerTab(TemplateRenderer $templateRenderer): string
    {
        $templateVars = ['friendlyUrls' => FriendlyUrl::fetchAll([], [], 'ORDER BY name')];
        return $templateRenderer->render('FriendlyUrl/PageManagerTab', $templateVars);
    }
}
