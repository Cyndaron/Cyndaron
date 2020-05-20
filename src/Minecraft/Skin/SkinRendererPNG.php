<?php
namespace Cyndaron\Minecraft\Skin;

use Symfony\Component\HttpFoundation\Response;

class SkinRendererPNG extends SkinRenderer
{
    /** @var resource */
    protected $image;

    protected function setupTarget(): void
    {
        $width = static::$maxX - static::$minX;
        $height = static::$maxY - static::$minY;
        $this->image = imagecreatetruecolor($this->parameters->ratio * $width + 1, $this->parameters->ratio * $height + 1);
        imagesavealpha($this->image, true);
        $trans_colour = imagecolorallocatealpha($this->image, 0, 0, 0, 127);
        imagefill($this->image, 0, 0, $trans_colour);
    }

    protected function addPolygon(Polygon $poly): void
    {
        $poly->addPngPolygon($this->image, static::$minX, static::$minY, $this->parameters->ratio);
    }

    protected function output(): Response
    {
        $this->headers['Content-Type'] = 'image/png';

        ob_start();
        imagepng($this->image);
        $contents = ob_get_clean();
        imagedestroy($this->image);
        for ($i = 1, $iMax = count($this->times); $i < $iMax; $i++)
        {
            $this->headers['generation-time-' . $i . '-' . $this->times[$i][0]] = ($this->times[$i][1] - $this->times[$i - 1][1]) * 1000 . 'ms';
        }
        $this->headers['generation-time-' . count($this->times) . '-TOTAL'] = ($this->times[count($this->times) - 1][1] - $this->times[0][1]) * 1000 . 'ms';
        return new Response($contents, Response::HTTP_OK, $this->headers);
    }
}
