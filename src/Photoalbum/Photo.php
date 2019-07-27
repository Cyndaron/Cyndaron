<?php
namespace Cyndaron\Photoalbum;

class Photo
{
    public $filename;
    public $hash;

    /**
     * @var Photoalbum $album
     */
    public $album;

    /**
     * @var PhotoalbumCaption|null $caption
     */
    public $caption = null;

    /**
     * @var string $link
     */
    public $link = '';

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
}