<?php
namespace Cyndaron;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigHelper extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('getCSRFToken', [\Cyndaron\User\User::class, 'getCSRFToken']),
            new TwigFunction('getSetting', [\Cyndaron\Setting::class, 'get']),
            new TwigFunction('button', [\Cyndaron\Widget\Button::class, 'create']),
            new TwigFunction('file_exists', 'file_exists'),
            new TwigFunction('ltrim', 'ltrim'),
            new TwigFunction('rtrim', 'rtrim'),
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('boolToText', [\Cyndaron\Util::class, 'boolToText']),
            new TwigFilter('var_dump', 'var_dump'),
        ];
    }
}