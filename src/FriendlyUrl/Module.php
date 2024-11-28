<?php
namespace Cyndaron\FriendlyUrl;

use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\View\Template\TemplateRenderer;

final class Module implements Datatypes, Routes
{
    /**
     * @inheritDoc
     */
    public function dataTypes(): array
    {
        return [
            'friendlyurl' => new Datatype(
                singular: 'Friendly URL',
                plural: 'Friendly URL\'s',
                pageManagerTab: self::pageManagerTab(...),
                pageManagerJS: '/src/FriendlyUrl/js/PageManagerTab.js',
            ),
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

    public static function pageManagerTab(TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler): string
    {
        $templateVars = [
            'friendlyUrls' => FriendlyUrl::fetchAll([], [], 'ORDER BY name'),
            'tokenAdd' => $tokenHandler->get('friendlyurl', 'add'),
            'tokenDelete' => $tokenHandler->get('friendlyurl', 'delete'),
            'tokenAddToMenu' => $tokenHandler->get('friendlyurl', 'addtomenu'),
        ];
        return $templateRenderer->render('FriendlyUrl/PageManagerTab', $templateVars);
    }
}
