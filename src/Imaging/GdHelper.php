<?php
declare(strict_types=1);

namespace Cyndaron\Imaging;

use Exception;
use finfo;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\ImageException;
use function Safe\file_get_contents;
use function Safe\imagecreatefrombmp;
use function Safe\imagecreatefromgif;
use function Safe\imagecreatefromjpeg;
use function Safe\imagecreatefrompng;
use function Safe\imagecreatefromwebp;

final class GdHelper
{
    /**
     * @param string $filename
     * @throws ImageException
     * @return \GdImage
     */
    public static function fromFilename(string $filename): \GdImage
    {
        try
        {
            $buffer = file_get_contents($filename);
        }
        catch (FilesystemException)
        {
            throw new ImageException('Kon de inhoud van het bestand niet lezen!');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($buffer);
        return match ($mimeType)
        {
            'image/bmp' => imagecreatefrombmp($filename),
            'image/gif' => imagecreatefromgif($filename),
            'image/jpeg' => imagecreatefromjpeg($filename),
            'image/png' => imagecreatefrompng($filename),
            'image/webp' => imagecreatefromwebp($filename),
            default => throw new ImageException('Ongeldig bestandstype'),
        };
    }
}
