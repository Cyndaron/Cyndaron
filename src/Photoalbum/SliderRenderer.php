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
        private readonly PhotoRepository $photoRepository,
    ) {
    }

    public function process(string $text): string
    {
        return preg_replace_callback('/%slider\|(\d+)%/', function($matches)
        {
            $album = $this->photoalbumRepository->fetchById((int)$matches[1]);
            if ($album !== null)
            {
                $photos = $this->photoRepository->fetchAllByAlbum($album);
                return $this->templateRenderer->render('Photoalbum/Photoslider', ['album' => $album, 'photos' => $photos]);
            }
            return '';
        }, $text) ?? $text;
    }
}
