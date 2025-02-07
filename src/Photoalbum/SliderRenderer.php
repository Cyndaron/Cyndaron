<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Cyndaron\View\Template\TemplateRenderer;
use function preg_replace_callback;

final class SliderRenderer implements \Cyndaron\Module\TextPostProcessor
{
    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
        private readonly PhotoalbumRepository $photoalbumRepository,
        private readonly PhotoalbumPage $photoalbumPage,
    ) {
    }

    public function process(string $text): string
    {
        return preg_replace_callback('/%slider\|(\d+)%/', function($matches)
        {
            $album = $this->photoalbumRepository->fetchById((int)$matches[1]);
            if ($album !== null)
            {
                return $this->photoalbumPage->drawSlider($album, $this->templateRenderer);
            }
            return '';
        }, $text) ?? $text;
    }
}
