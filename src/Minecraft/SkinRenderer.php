<?php
namespace Cyndaron\Minecraft;

/**
 * Class SkinRenderer
 */
class SkinRenderer
{
    public const SECONDS_TO_CACHE = 604800; // Cache for 7 days
    public const FALLBACK_IMAGE = __DIR__ . '/char.png';

    /**
     * @param string $skinUrl
     * @return resource
     */
    public static function getSkinOrFallback($skinUrl)
    {
        if (trim($skinUrl) === '')
        {
            $img_png = imagecreatefrompng(self::FALLBACK_IMAGE);
        }
        else
        {
            $img_png = imagecreatefrompng($skinUrl);
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
