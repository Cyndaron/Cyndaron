<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use function preg_replace_callback;

final class SliderRenderer implements \Cyndaron\Module\TextPostProcessor
{
    public function process(string $text): string
    {
        return preg_replace_callback('/%slider\|(\d+)%/', static function($matches)
        {
            $album = Photoalbum::fetchById((int)$matches[1]);
            if ($album !== null)
            {
                $page = new PhotoalbumPage($album, null, Photoalbum::VIEWMODE_PORTFOLIO);
                return $page->drawSlider($album);
            }
            return '';
        }, $text) ?? $text;
    }
}
