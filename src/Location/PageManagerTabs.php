<?php
declare(strict_types=1);

namespace Cyndaron\Location;

use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\View\Template\TemplateRenderer;

class PageManagerTabs
{
    public static function locationsTab(TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler): string
    {
        $locations = Location::fetchAll();
        $ret = $templateRenderer->render('Location/PageManagerTab', [
            'locations' => $locations,
            'tokenDelete' => $tokenHandler->get('location', 'delete'),
        ]);
        return $ret;
    }
}
