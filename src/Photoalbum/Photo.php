<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\Imaging\ImageTransformer;
use Cyndaron\Util\Util;

use Safe\Exceptions\PcreException;
use function Safe\copy;
use function Safe\md5_file;
use function Safe\preg_match;
use function Safe\unlink;
use function basename;
use function count;
use function move_uploaded_file;
use function file_exists;

final class Photo
{
    public const MAX_DIMENSION = 1280;

    public string $filename;
    public string $hash;
    public Photoalbum $album;
    public PhotoalbumCaption|null $caption = null;
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
            $photo->caption = PhotoalbumCaption::fetchByHash($photo->hash);
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
        self::createThumbnail($filenameThumb, $album->thumbnailWidth, $album->thumbnailHeight);
    }

    protected static function resizeMainPhoto(string $filename): bool
    {
        $transformer = ImageTransformer::fromFilename($filename);
        $transformer->autoRotate();
        $image = $transformer->getImage();
        if ($image->getImageWidth() > self::MAX_DIMENSION || $image->getImageHeight() > self::MAX_DIMENSION)
        {
            $image->scaleImage(self::MAX_DIMENSION, self::MAX_DIMENSION, true);
        }
        return $image->writeImage($filename);
    }

    protected static function createThumbnail(string $filename, int $thumbnailWidth, int $thumbnailHeight): bool
    {
        $transformer = ImageTransformer::fromFilename($filename);
        $transformer->autoRotate();
        $image = $transformer->getImage();
        $image->cropThumbnailImage($thumbnailWidth, $thumbnailHeight);
        return $image->writeImage($filename);
    }

    /**
     * @param Photoalbum $album
     * @param string $filename
     * @throws \Safe\Exceptions\FilesystemException If the photo or thumbnail exists, but cannot be removed.
     * @return int
     */
    public static function deleteByAlbumAndFilename(Photoalbum $album, string $filename): int
    {
        $numDeleted = 0;

        $baseDir = Util::UPLOAD_DIR . "/photoalbums/{$album->id}";
        $mainPhoto = "$baseDir/originals/$filename";
        if (file_exists($mainPhoto))
        {
            unlink($mainPhoto);
            $numDeleted++;
        }
        $thumbnail = "$baseDir/thumbnails/$filename";
        if (file_exists($thumbnail))
        {
            unlink($thumbnail);
            $numDeleted++;
        }

        return $numDeleted;
    }

    public function getUrl(): string
    {
        if ($this->caption === null)
        {
            return '';
        }

        try
        {
            $result = preg_match(
                '@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@i',
                $this->caption->caption,
                $matches
            );
            if ($result === 0 || count($matches) < 2)
            {
                return '';
            }
        }
        catch (PcreException $e)
        {
            return '';
        }

        return $matches[1];
    }
}
