<?php
declare(strict_types=1);

namespace Cyndaron\Imaging;

use Imagick;

final class ImageTransformer
{
    public function __construct(private readonly Imagick $image)
    {
    }

    /**
     * Courtesy of https://www.php.net/manual/en/imagick.getimageorientation.php#111448.
     *
     * @throws \ImagickException
     * @return void
     */
    public function autoRotate(): void
    {
        $orientation = $this->image->getImageOrientation();
        switch ($orientation)
        {
            case Imagick::ORIENTATION_BOTTOMRIGHT:
                $this->image->rotateImage('#000', 180);
                break;

            case Imagick::ORIENTATION_RIGHTTOP:
                $this->image->rotateImage('#000', 90);
                break;

            case Imagick::ORIENTATION_LEFTBOTTOM:
                $this->image->rotateImage('#000', -90);
                break;
        }

        // Now that it's auto-rotated, make sure the EXIF data is correct in case the EXIF gets saved with the image!
        $this->image->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
    }

    public function getImage(): Imagick
    {
        return $this->image;
    }

    public static function fromFilename(string $filename): self
    {
        return new self(new Imagick($filename));
    }
}
