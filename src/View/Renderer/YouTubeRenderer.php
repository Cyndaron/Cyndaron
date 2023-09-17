<?php
declare(strict_types=1);

namespace Cyndaron\View\Renderer;

use Cyndaron\Module\TextPostProcessor;
use function Safe\preg_replace;

final class YouTubeRenderer implements TextPostProcessor
{
    private const TAG = '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="https://www.youtube.com/embed/$1" sandbox="allow-scripts allow-same-origin allow-popups allow-presentation" allowfullscreen></iframe></div>';

    public function process(string $text): string
    {
        /** @var string $replaced */
        $replaced = preg_replace('/%youtube\|([A-Za-z0-9_\-]+)%/', self::TAG, $text);
        return $replaced;
    }
}
