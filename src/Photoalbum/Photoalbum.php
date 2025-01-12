<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Cyndaron\Category\ModelWithCategory;
use Cyndaron\DBAL\DatabaseField;
use Cyndaron\Url\Url;
use Cyndaron\Url\UrlService;
use Cyndaron\Util\Error\IncompleteData;
use Cyndaron\Util\Util;
use function array_filter;
use function array_values;
use function count;
use function natsort;
use function reset;
use function Safe\error_log;
use function Safe\scandir;
use function str_replace;
use function substr;
use const PUB_DIR;

final class Photoalbum extends ModelWithCategory
{
    public const TABLE = 'photoalbums';
    public const CATEGORY_TABLE = 'photoalbum_categories';

    public const VIEWMODE_REGULAR = 0;
    public const VIEWMODE_PORTFOLIO = 1;

    public const VIEWMODE_DESCRIPTIONS = [
        self::VIEWMODE_REGULAR => 'Fotoalbum',
        self::VIEWMODE_PORTFOLIO => 'Portfolio',
    ];

    public const RIGHT_EDIT = 'photoalbum_edit';
    public const RIGHT_UPLOAD = 'photoalbum_upload';

    #[DatabaseField]
    public string $notes = '';
    #[DatabaseField]
    public bool $hideFromOverview = false;
    #[DatabaseField]
    public int $viewMode = self::VIEWMODE_REGULAR;
    #[DatabaseField]
    public int $thumbnailWidth = 270;
    #[DatabaseField]
    public int $thumbnailHeight = 200;

    public static function create(string $name, string $notes = '', bool $showBreadcrumbs = false): int|null
    {
        if ($name === '')
        {
            throw new IncompleteData('Empty photo album name!');
        }

        $album = new Photoalbum();
        $album->name = $name;
        $album->notes = $notes;
        $album->showBreadcrumbs = $showBreadcrumbs;
        $album->save();

        $id = $album->id;
        if ($id !== null)
        {
            $baseDir = self::getPhotoalbumsDir() . "/{$id}";
            Util::createDir($baseDir);
            Util::createDir("{$baseDir}/originals");
            Util::createDir("{$baseDir}/thumbnails");
        }

        return $id ?: null;
    }

    public function getFriendlyUrl(UrlService $urlService): Url
    {
        $url = new Url('/photoalbum/' . $this->id);
        return $urlService->toFriendly($url);
    }

    /**
     * @throws \Safe\Exceptions\ErrorfuncException
     * @return string[]
     */
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
