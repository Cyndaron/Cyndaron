<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\Util;
use Imagick;

class Photo
{
    const THUMBNAIL_WIDTH = 270;
    const THUMBNAIL_HEIGHT = 200;

    public string $filename;
    public string $hash;
    public Photoalbum $album;
    public ?PhotoalbumCaption $caption = null;
    public string $link = '';

    /**
     * @param Photoalbum $album
     * @return self[]
     */
    public static function fetchAllByAlbum(Photoalbum $album)
    {
        $ret = [];
        foreach ($album->getPhotos() as $filename)
        {
            $photo = new Photo();
            $photo->album = $album;
            $photo->filename = $filename;
            $photo->hash = md5_file($photo->getFullPath());
            $photo->caption = PhotoalbumCaption::loadByHash($photo->hash);
            $ret[] = $photo;
        }

        return $ret;
    }

    public function getFullPath()
    {
        return $this->album->getLinkPrefix() . $this->filename;
    }

    public function getThumbnailPath()
    {
        return $this->album->getThumbnailPrefix() . $this->filename;
    }

    public static function create(Photoalbum $album)
    {
        $photoalbumPath = __DIR__ . '/../../fotoalbums/' . $album->id;
        $photoalbumThumbnailsPath = $photoalbumPath . 'thumbnails';

        Util::createDir($photoalbumPath);
        Util::createDir($photoalbumThumbnailsPath);

        $origname = $_FILES['newFile']['name'];
        $filename =  "$photoalbumPath/$origname";
        while (file_exists($filename))
        {
            $filename = $photoalbumPath . '/_' . basename($filename);
        }

        $filenameThumb = "$photoalbumThumbnailsPath/" . basename($filename);

        if (move_uploaded_file($_FILES['newFile']['tmp_name'], $filename))
        {
            copy($filename, $filenameThumb);

            static::resizeMainPhoto($filename);
            static::createThumbnail($filenameThumb);
        }
        else
        {
            die('Kon foto niet uploaden!');
        }
    }

    protected static function resizeMainPhoto(string $filename)
    {
        $image = new Imagick($filename);
        static::autoRotate($image);
        $image->scaleImage(1024, 1024, true);
        $image->writeImage($filename);
    }

    protected static function createThumbnail(string $filename)
    {
        $image = new Imagick($filename);
        static::autoRotate($image);
        $image->scaleImage(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT);
        $x = ($image->getImageWidth() - self::THUMBNAIL_WIDTH) / 2;
        $y = ($image->getImageHeight() - self::THUMBNAIL_HEIGHT) / 2;
        $image->cropImage(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT, $x, $y);
        $image->writeImage($filename);
    }

    /**
     * Courtesy of https://www.php.net/manual/en/imagick.getimageorientation.php#111448.
     *
     * @param Imagick $image
     */
    protected static function autoRotate(Imagick &$image)
    {
        $orientation = $image->getImageOrientation();

        switch($orientation) {
            case imagick::ORIENTATION_BOTTOMRIGHT:
                $image->rotateimage("#000", 180);
                break;

            case imagick::ORIENTATION_RIGHTTOP:
                $image->rotateimage("#000", 90);
                break;

            case imagick::ORIENTATION_LEFTBOTTOM:
                $image->rotateimage("#000", -90);
                break;
        }

        // Now that it's auto-rotated, make sure the EXIF data is correct in case the EXIF gets saved with the image!
        $image->setImageOrientation(imagick::ORIENTATION_TOPLEFT);
    }

    public static function deleteByAlbumAndFilename(Photoalbum $album, string $filename): int
    {
        $numDeleted = 0;

        $path = __DIR__ . "/../../fotoalbums/{$album->id}";
        $mainPhoto = "$path/$filename";
        if (file_exists($mainPhoto))
        {
            if (unlink($mainPhoto))
            {
                $numDeleted++;
            }
        }
        $thumbnail = "{$path}thumbnails/$filename";
        if (file_exists($thumbnail))
        {
            if (unlink($thumbnail))
            {
                $numDeleted++;
            }
        }

        return $numDeleted;
    }
}