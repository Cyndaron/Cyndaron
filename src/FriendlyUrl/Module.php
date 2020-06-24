<?php
namespace Cyndaron\FriendlyUrl;

use Cyndaron\DBConnection;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;
use Cyndaron\PageManager\PageManagerPage;
use Cyndaron\Template\Template;

class Module implements Datatypes, Routes
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

    public static function pageManagerTab(): string
    {
        $templateVars = ['friendlyUrls' => FriendlyUrl::fetchAll([], [], 'ORDER BY name')];
        return (new Template())->render('FriendlyUrl/PageManagerTab', $templateVars);
    }
}
