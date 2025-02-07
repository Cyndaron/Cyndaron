<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Cyndaron\Imaging\ImageTransformer;
use Cyndaron\Util\Util;
use function Safe\copy;
use function Safe\md5_file;
use function Safe\unlink;
use function basename;
use function move_uploaded_file;
use function file_exists;

final class PhotoRepository
{
    private const MAX_DIMENSION = 1280;

    public function __construct(
        private readonly PhotoalbumCaptionRepository $photoalbumCaptionRepository
    ) {
    }

    /**
     * @param Photoalbum $album
     *@throws \Safe\Exceptions\StringsException|\Safe\Exceptions\ErrorfuncException
     * @return Photo[]
     */
    public function fetchAllByAlbum(Photoalbum $album): array
    {
        $ret = [];
        foreach ($album->getPhotos() as $filename)
        {
            $photo = new Photo();
            $photo->album = $album;
            $photo->filename = $filename;
            $photo->hash = md5_file($photo->getFullPath());
            $photo->caption = $this->photoalbumCaptionRepository->fetchByHash($photo->hash);
            $ret[] = $photo;
        }

        return $ret;
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

        self::resizeMainPhoto($filename, self::MAX_DIMENSION);
        self::createThumbnail($filenameThumb, $album->thumbnailWidth, $album->thumbnailHeight);
    }

    protected static function resizeMainPhoto(string $filename, int $maxDimension): bool
    {
        $transformer = ImageTransformer::fromFilename($filename);
        $transformer->autoRotate();
        $image = $transformer->getImage();
        if ($image->getImageWidth() > $maxDimension || $image->getImageHeight() > $maxDimension)
        {
            $image->scaleImage($maxDimension, $maxDimension, true);
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
}
