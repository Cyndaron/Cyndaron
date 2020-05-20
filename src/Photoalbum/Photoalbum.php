<?php
declare (strict_types = 1);

namespace Cyndaron\Photoalbum;

use Cyndaron\DBConnection;
use Cyndaron\Error\IncompleteData;
use Cyndaron\Model;
use Cyndaron\Url;
use Cyndaron\Util;
use Exception;

class Photoalbum extends Model
{
    public const TABLE = 'photoalbums';
    public const TABLE_FIELDS = ['name', 'notes', 'categoryId', 'showBreadcrumbs', 'hideFromOverview', 'viewMode'];
    public const HAS_CATEGORY = true;

    public const VIEWMODE_REGULAR = 0;
    public const VIEWMODE_PORTFOLIO = 1;

    public const VIEWMODE_DESCRIPTIONS = [
        self::VIEWMODE_REGULAR => 'Fotoalbum',
        self::VIEWMODE_PORTFOLIO => 'Portfolio',
    ];

    public string $name = '';
    public string $notes = '';
    public ?int $categoryId = null;
    public bool $showBreadcrumbs = false;
    public bool $hideFromOverview = false;
    public int $viewMode = self::VIEWMODE_REGULAR;

    public static function create(string $name, string $notes = '', bool $showBreadcrumbs = false)
    {
        if ($name === '')
            throw new IncompleteData('Empty photo album name!');

        $id = DBConnection::doQuery('INSERT INTO photoalbums(`name`,`notes`,`showBreadcrumbs`) VALUES (?,?,?);', [$name, $notes,(int)$showBreadcrumbs]);
        if ($id !== false)
        {
            Util::createDir(__DIR__ . "/../../fotoalbums/${id}");
            Util::createDir(__DIR__ . "/../../fotoalbums/${id}thumbnails");
        }

        return $id;
    }

    public function getFriendlyUrl(): string
    {
        $url = new Url('/photoalbum/' . $this->id);
        return $url->getFriendly();
    }

    public function getPhotos(): array
    {
        $ret = [];

        if ($dirArray = @scandir("./fotoalbums/$this->id"))
        {
            natsort($dirArray);
            $ret = array_values(array_filter($dirArray, static function($value) {
                return substr($value, 0, 1) !== '.';
            }));
        }

        return $ret;
    }

    public function getLinkPrefix(): string
    {
        return 'fotoalbums/' . $this->id . '/';
    }

    public function getThumbnailPrefix(): string
    {
        return 'fotoalbums/' . $this->id . 'thumbnails/';
    }
}