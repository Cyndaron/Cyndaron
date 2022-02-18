<?php
declare(strict_types=1);

namespace Cyndaron\Barcode;

use GdImage;
use function assert;
use function imagecreate;
use function imagepng;
use function ob_get_clean;
use function Safe\imagedestroy;
use function strlen;
use function substr;
use function ob_start;
use function imagecolorallocate;
use function imagefill;
use function imagestring;
use function imagefilledrectangle;
use function ceil;

/**
 * @author David S. Tufts, Michael Steenbeek
 * @company davidscotttufts.com
 * @license https://github.com/davidscotttufts/php-barcode/blob/master/LICENSE
 */
abstract class Barcode
{
    protected string $text;
    protected int $size;
    protected bool $print;
    protected float $sizeFactor;
    protected string $orientation;

    /** @var GdImage  */
    protected $image;

    public function __construct(
        string $text,
        int $size = 20,
        bool $print = false,
        float $sizeFactor = 1.0,
        string $orientation = Orientation::HORIZONTAL
    ) {
        $this->text = $text;
        $this->size = $size;
        $this->print = $print;
        $this->sizeFactor = $sizeFactor;
        $this->orientation = $orientation;

        $codeString = $this->getCodeString();
        $this->createImage($codeString);
    }


    public function __destruct()
    {
        imagedestroy($this->image);
    }

    public function getOutput(): string
    {
        ob_start();
        imagepng($this->image);
        return ob_get_clean() ?: '';
    }

    public function toFile(string $filepath): void
    {
        imagepng($this->image, $filepath);
    }

    protected function createImage(string $codeString): void
    {
        // Pad the edges of the barcode
        $codeLength = 20;
        if ($this->print)
        {
            $textHeight = 30;
        }
        else
        {
            $textHeight = 0;
        }

        $strlen = strlen($codeString);
        for ($i = 1; $i <= $strlen; $i++)
        {
            $codeLength += (int)(substr($codeString, ($i - 1), 1));
        }

        if ($this->orientation === Orientation::HORIZONTAL)
        {
            $imgWidth = (int)ceil($codeLength * $this->sizeFactor);
            $imgHeight = $this->size;
        }
        else
        {
            $imgWidth = $this->size;
            $imgHeight = (int)ceil($codeLength * $this->sizeFactor);
        }

        $image = imagecreate($imgWidth, $imgHeight + $textHeight);
        assert($image !== false);
        $this->image = $image;
        $black = imagecolorallocate($this->image, 0, 0, 0);
        assert($black !== false);
        $white = imagecolorallocate($this->image, 255, 255, 255);
        assert($white !== false);

        imagefill($this->image, 0, 0, $white);
        if ($this->print)
        {
            imagestring($this->image, 5, 31, $imgHeight, $this->text, $black);
        }

        $location = 10;
        $strlen = strlen($codeString);
        for ($position = 1; $position <= $strlen; $position++)
        {
            $curSize = $location + (substr($codeString, ($position - 1), 1));
            $stripeColor = ($position % 2 === 0 ? $white : $black);
            if ($this->orientation === Orientation::HORIZONTAL)
            {
                imagefilledrectangle($this->image, (int)ceil($location * $this->sizeFactor), 0, (int)ceil($curSize * $this->sizeFactor), $imgHeight, $stripeColor);
            }
            else
            {
                imagefilledrectangle($this->image, 0, (int)ceil($location * $this->sizeFactor), $imgWidth, (int)ceil($curSize * $this->sizeFactor), $stripeColor);
            }
            $location = $curSize;
        }
    }

    abstract protected function getCodeString(): string;
}
