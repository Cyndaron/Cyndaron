<?php
declare(strict_types=1);

namespace Cyndaron\View\Renderer;

use Cyndaron\Module\TextPostProcessor;
use function Safe\preg_replace;

final class YouTubeRenderer implements TextPostProcessor
{
    private const SINGLE_VIDEO_TAG = '<div class="ratio ratio-16x9"><iframe src="https://www.youtube-nocookie.com/embed/$1" sandbox="allow-scripts allow-same-origin allow-popups allow-presentation" allowfullscreen></iframe></div>';
    private const PLAYLIST_TAG = '<div class="ratio ratio-16x9"><iframe src="https://www.youtube-nocookie.com/embed/videoseries?list=$1" sandbox="allow-scripts allow-same-origin allow-popups allow-presentation" allowfullscreen></iframe></div>';

    public function process(string $text): string
    {
        /** @var string $replaced */
        $replaced = preg_replace('/%youtube\|([A-Za-z0-9_\-]{20,})%/', self::PLAYLIST_TAG, $text);
        /** @var string $replaced */
        $replaced = preg_replace('/%youtube\|([A-Za-z0-9_\-]+)%/', self::SINGLE_VIDEO_TAG, $replaced);
        return $replaced;
    }
}
