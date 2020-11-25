<?php
namespace Cyndaron\Minecraft\Skin;

use Safe\Exceptions\ImageException;
use function Safe\imagealphablending;
use function Safe\imagecreatefrompng;
use function Safe\imagesavealpha;
use function trim;

/**
 * Class Skin
 */
final class Skin
{
    public const SECONDS_TO_CACHE = 604800; // Cache for 7 days
    public const FALLBACK_IMAGE = __DIR__ . '/char.png';

    private ?string $url;

    public function __construct(?string $skinUrl)
    {
        $this->url = $skinUrl;
    }

    /**
     * @throws ImageException
     * @return resource
     */
    public function getSkinOrFallback()
    {
        if ($this->url === null || trim($this->url) === '')
        {
            $img_png = imagecreatefrompng(self::FALLBACK_IMAGE);
        }
        else
        {
            try
            {
                $img_png = imagecreatefrompng($this->url);
            }
            catch (ImageException $e)
            {
                $img_png = imagecreatefrompng(self::FALLBACK_IMAGE);
            }
        }

        imagealphablending($img_png, true);
        imagesavealpha($img_png, true);
        return $img_png;
    }
}
