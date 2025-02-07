<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Safe\Exceptions\PcreException;
use function Safe\preg_match;
use function count;
use function file_exists;

final class Photo
{
    public string $filename;
    public string $hash;
    public Photoalbum $album;
    public PhotoalbumCaption|null $caption = null;
    public string $link = '';

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
        catch (PcreException)
        {
            return '';
        }

        return $matches[1];
    }
}
