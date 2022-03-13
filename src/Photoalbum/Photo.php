<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\Util\Util;
use Imagick;

use function Safe\copy;
use function Safe\md5_file;
use function basename;
use function move_uploaded_file;
use function file_exists;

final class Photo
{
    public const THUMBNAIL_WIDTH = 270;
    public const THUMBNAIL_HEIGHT = 200;
    public const MAX_DIMENSION = 1024;

    public string $filename;
    public string $hash;
    public Photoalbum $album;
    public ?PhotoalbumCaption $caption = null;
    public string $link = '';

    /**
     * @param Photoalbum $album
     * @throws \Safe\Exceptions\StringsException
     * @return self[]
     */
    public static function fetchAllByAlbum(Photoalbum $album): array
    {
        $ret = [];
        foreach ($album->getPhotos() as $filename)
        {
            $photo = new self();
            $photo->album = $album;
            $photo->filename = $filename;
            $photo->hash = md5_file($photo->getFullPath());
            $photo->caption = PhotoalbumCaption::loadByHash($photo->hash);
            $ret[] = $photo;
        }

        return $ret;
    }

    public function getFullPath(): string
    {
        return Photoalbum::getPhotoalbumsDir() . $this->album->id . '/originals/' . $this->filename;
    }

    public function hasThumbnail(): bool
    {
        return file_exists(Photoalbum::getPhotoalbumsDir() . $this->album->id . '/thumbnails/' . $this->filename);
    }

    public function getRelativeThumbnailPath(): string
    {
        return $this->album->getThumbnailPrefix() . $this->filename;
    }

    public static function create(Photoalbum $album, string $tmpName, string $proposedName): void
    {
        $baseDir = Util::UPLOAD_DIR . '/photoalbums/' . $album->id;
        $photoalbumPath = $baseDir . '/originals';
        $photoalbumThumbnailsPath = $baseDir . '/thumbnails';

        Util::createDir($photoalbumPath);
        Util::createDir($photoalbumThumbnailsPath);

        $filename =  "$photoalbumPath/$proposedName";
        while (file_exists($filename))
        {
            $filename = $photoalbumPath . '/_' . basename($filename);
        }

        $filenameThumb = "$photoalbumThumbnailsPath/" . basename($filename);

        if (!move_uploaded_file($tmpName, $filename))
        {
            throw new \Exception('Kon foto niet uploaden!');
        }

        copy($filename, $filenameThumb);

        self::resizeMainPhoto($filename);
        self::createThumbnail($filenameThumb);
    }

    protected static function resizeMainPhoto(string $filename): bool
    {
        $image = new Imagick($filename);
        self::autoRotate($image);
        if ($image->getImageWidth() > self::MAX_DIMENSION || $image->getImageHeight() > self::MAX_DIMENSION)
        {
            $image->scaleImage(self::MAX_DIMENSION, self::MAX_DIMENSION, true);
        }
        return $image->writeImage($filename);
    }

    protected static function createThumbnail(string $filename): bool
    {
        $image = new Imagick($filename);
        self::autoRotate($image);
        $image->cropThumbnailImage(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT);
        return $image->writeImage($filename);
    }

    /**
     * Courtesy of https://www.php.net/manual/en/imagick.getimageorientation.php#111448.
     *
     * @param Imagick $image
     */
    protected static function autoRotate(Imagick $image): void
    {
        $orientation = $image->getImageOrientation();

        switch ($orientation)
        {
            case Imagick::ORIENTATION_BOTTOMRIGHT:
                $image->rotateImage('#000', 180);
                break;

            case Imagick::ORIENTATION_RIGHTTOP:
                $image->rotateImage('#000', 90);
                break;

            case Imagick::ORIENTATION_LEFTBOTTOM:
                $image->rotateImage('#000', -90);
                break;
        }

        // Now that it's auto-rotated, make sure the EXIF data is correct in case the EXIF gets saved with the image!
        $image->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
    }

    public static function deleteByAlbumAndFilename(Photoalbum $album, string $filename): int
    {
        $numDeleted = 0;

        $baseDir = Util::UPLOAD_DIR . "/photoalbums/{$album->id}";
        $mainPhoto = "$baseDir/originals/$filename";
        if (file_exists($mainPhoto) && Util::deleteFile($mainPhoto))
        {
            $numDeleted++;
        }
        $thumbnail = "$baseDir/thumbnails/$filename";
        if (file_exists($thumbnail) && Util::deleteFile($thumbnail))
        {
            $numDeleted++;
        }

        return $numDeleted;
    }
}
