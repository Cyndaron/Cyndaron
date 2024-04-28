<?php
declare(strict_types=1);

namespace Cyndaron\Location;

use Cyndaron\View\Template\TemplateRenderer;

class PageManagerTabs
{
    public static function locationsTab(TemplateRenderer $templateRenderer): string
    {
        $locations = Location::fetchAll();
        $ret = $templateRenderer->render('Location/PageManagerTab', ['locations' => $locations]);
        return $ret;
    }
}
