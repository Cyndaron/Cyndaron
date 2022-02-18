<?php
namespace Cyndaron\Minecraft\Skin;

use Symfony\Component\HttpFoundation\Response;

use function Safe\imagecreatetruecolor;
use function Safe\imagedestroy;
use function Safe\imagefill;
use function Safe\imagepng;
use function Safe\imagesavealpha;
use function imagecolorallocatealpha;
use function Safe\ob_start;
use function ob_get_clean;
use function count;
use function assert;

final class SkinRendererPNG extends SkinRenderer
{
    /** @var resource */
    protected $image;

    protected function setupTarget(): void
    {
        $width = self::$maxX - self::$minX;
        $height = self::$maxY - self::$minY;
        $this->image = imagecreatetruecolor($this->parameters->ratio * $width + 1, $this->parameters->ratio * $height + 1);
        imagesavealpha($this->image, true);
        $transColour = imagecolorallocatealpha($this->image, 0, 0, 0, 127);
        assert($transColour !== false);
        imagefill($this->image, 0, 0, $transColour);
    }

    protected function addPolygon(Polygon $poly): void
    {
        $poly->addPngPolygon($this->image, self::$minX, self::$minY, $this->parameters->ratio);
    }

    protected function output(): Response
    {
        $this->headers['Content-Type'] = 'image/png';

        ob_start();
        imagepng($this->image);
        $contents = ob_get_clean() ?: '';
        imagedestroy($this->image);
        for ($i = 1, $iMax = count($this->times); $i < $iMax; $i++)
        {
            $this->headers['generation-time-' . $i . '-' . $this->times[$i][0]] = ($this->times[$i][1] - $this->times[$i - 1][1]) * 1000 . 'ms';
        }
        $this->headers['generation-time-' . count($this->times) . '-TOTAL'] = ($this->times[count($this->times) - 1][1] - $this->times[0][1]) * 1000 . 'ms';
        return new Response($contents, Response::HTTP_OK, $this->headers);
    }
}
