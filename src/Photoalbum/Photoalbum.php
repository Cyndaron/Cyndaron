<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Cyndaron\DBConnection;
use Cyndaron\Error\IncompleteData;
use Cyndaron\ModelWithCategory;
use Cyndaron\Url;
use Cyndaron\Util;

use function Safe\natsort;
use function Safe\scandir;
use function Safe\substr;

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

        if ($dirArray = @scandir(Util::UPLOAD_DIR . "/photoalbums/$this->id/originals"))
        {
            natsort($dirArray);
            $ret = array_values(array_filter($dirArray, static function($value)
            {
                return substr($value, 0, 1) !== '.';
            }));
        }

        return $ret;
    }

    public function getLinkPrefix(): string
    {
        return self::getPhotoalbumsDir() . $this->id . '/originals/';
    }

    public function getThumbnailPrefix(): string
    {
        return self::getPhotoalbumsDir() . $this->id . '/thumbnails/';
    }

    public static function getPhotoalbumsDir(): string
    {
        return Util::UPLOAD_DIR . '/photoalbums/';
    }

    public function getText(): string
    {
        return $this->notes;
    }

    public function getImage(): string
    {
        $image = parent::getImage();
        if ($image !== '')
        {
            return $image;
        }

        $photos = $this->getPhotos();
        $photo1 = reset($photos);
        return $this->getLinkPrefix() . $photo1;
    }
}
