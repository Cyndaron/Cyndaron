<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Util\Error\IncompleteData;
use Cyndaron\Category\ModelWithCategory;
use Cyndaron\Url;
use Cyndaron\Util\Util;

use function Safe\error_log;
use function Safe\natsort;
use function Safe\scandir;
use function Safe\substr;
use function array_values;
use function array_filter;
use function reset;
use const PUB_DIR;

final class Photoalbum extends ModelWithCategory
{
    public const TABLE = 'photoalbums';
    public const CATEGORY_TABLE = 'photoalbum_categories';
    public const TABLE_FIELDS = ['name', 'image', 'previewImage', 'blurb', 'notes', 'showBreadcrumbs', 'hideFromOverview', 'viewMode'];

    public const VIEWMODE_REGULAR = 0;
    public const VIEWMODE_PORTFOLIO = 1;

    public const VIEWMODE_DESCRIPTIONS = [
        self::VIEWMODE_REGULAR => 'Fotoalbum',
        self::VIEWMODE_PORTFOLIO => 'Portfolio',
    ];

    public string $notes = '';
    public bool $hideFromOverview = false;
    public int $viewMode = self::VIEWMODE_REGULAR;

    public static function create(string $name, string $notes = '', bool $showBreadcrumbs = false): ?int
    {
        if ($name === '')
        {
            throw new IncompleteData('Empty photo album name!');
        }

        $id = DBConnection::doQuery('INSERT INTO photoalbums(`name`,`notes`,`showBreadcrumbs`) VALUES (?,?,?);', [$name, $notes,(int)$showBreadcrumbs]);
        if ($id !== false)
        {
            $baseDir = self::getPhotoalbumsDir() . "/{$id}";
            Util::createDir($baseDir);
            Util::createDir("{$baseDir}/originals");
            Util::createDir("{$baseDir}/thumbnails");
        }

        return $id ?: null;
    }

    public function getFriendlyUrl(): string
    {
        $url = new Url('/photoalbum/' . $this->id);
        return $url->getFriendly();
    }

    public function getPhotos(): array
    {
        $ret = [];

        try
        {
            $dirArray = scandir(Util::UPLOAD_DIR . "/photoalbums/$this->id/originals");
            if ($dirArray !== [])
            {
                natsort($dirArray);
                $ret = array_values(array_filter($dirArray, static function($value)
                {
                    return substr($value, 0, 1) !== '.';
                }));
            }
        }
        catch (\Throwable $e)
        {
            error_log((string)$e);
        }

        return $ret;
    }

    public function getLinkPrefix(): string
    {
        return self::getPhotoalbumsRelative() . $this->id . '/originals/';
    }

    public function getThumbnailPrefix(): string
    {
        return self::getPhotoalbumsRelative() . $this->id . '/thumbnails/';
    }

    public static function getPhotoalbumsDir(): string
    {
        return Util::UPLOAD_DIR . '/photoalbums/';
    }

    public static function getPhotoalbumsRelative(): string
    {
        return str_replace(PUB_DIR, '', self::getPhotoalbumsDir());
    }

    public function getText(): string
    {
        return $this->notes;
    }

    public function getPreviewImage(): string
    {
        if ($this->previewImage !== '')
        {
            return $this->previewImage;
        }

        $photos = $this->getPhotos();
        if (count($photos) === 0)
        {
            return parent::getPreviewImage();
        }

        $photo1 = reset($photos);
        return $this->getThumbnailPrefix() . $photo1;
    }
}
