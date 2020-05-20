<?php
namespace Cyndaron\Minecraft;

/**
 * Class Skin
 */
class Skin
{
    public const SECONDS_TO_CACHE = 604800; // Cache for 7 days
    public const FALLBACK_IMAGE = __DIR__ . '/char.png';

    private ?string $url;

    public function __construct(?string $skinUrl)
    {
        $this->url = $skinUrl;
    }

    /**
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
            $img_png = imagecreatefrompng($this->url);
            if (!$img_png)
            {
                $img_png = imagecreatefrompng(self::FALLBACK_IMAGE);
            }
        }

        imagealphablending($img_png, true);
        imagesavealpha($img_png, true);
        return $img_png;
    }
}
