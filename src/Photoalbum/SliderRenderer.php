<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Cyndaron\View\Renderer\TextRenderer;
use Cyndaron\View\Template\TemplateRenderer;
use function preg_replace_callback;

final class SliderRenderer implements \Cyndaron\Module\TextPostProcessor
{
    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
        private readonly TextRenderer $textRenderer,
    ) {
    }

    public function process(string $text): string
    {
        return preg_replace_callback('/%slider\|(\d+)%/', function($matches)
        {
            $album = Photoalbum::fetchById((int)$matches[1]);
            if ($album !== null)
            {
                $page = new PhotoalbumPage($album, $this->textRenderer, null, Photoalbum::VIEWMODE_PORTFOLIO);
                return $page->drawSlider($album, $this->templateRenderer);
            }
            return '';
        }, $text) ?? $text;
    }
}
