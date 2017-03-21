<?php
namespace Cyndaron\Minecraft;

/**
 * Class SkinRenderer
 */
class SkinRenderer
{
    const SECONDS_TO_CACHE = 604800; // Cache for 7 days
    const FALLBACK_IMAGE = __DIR__ . '/../../sys/minecraft/res/char.png';

    /**
     * @param string $username
     * @return resource
     */
    public static function getSkinImageByUsername($username = '')
    {
        $url = 'https://s3.amazonaws.com/MinecraftSkins/' . $username . '.png';
        if (trim($username) == '')
        {
            $img_png = imagecreatefrompng(self::FALLBACK_IMAGE);
        }
        else
        {
            $img_png = @imagecreatefrompng($url);
        }

        if (!$img_png)
        {
            $img_png = imagecreatefrompng(self::FALLBACK_IMAGE);
        }
        imagealphablending($img_png, true);
        imagesavealpha($img_png, true);
        return $img_png;
    }
}
