<?php
declare (strict_types = 1);

namespace Cyndaron\Photoalbum;

use Cyndaron\DBConnection;
use Cyndaron\Model;
use Cyndaron\Url;
use Cyndaron\Util;
use Exception;

class Photoalbum extends Model
{
    const TABLE = 'photoalbums';
    const TABLE_FIELDS = ['name', 'notes', 'categoryId', 'showBreadcrumbs', 'hideFromOverview', 'viewMode'];
    const HAS_CATEGORY = true;

    const VIEWMODE_REGULAR = 0;
    const VIEWMODE_PORTFOLIO = 1;

    const VIEWMODE_DESCRIPTIONS = [
        self::VIEWMODE_REGULAR => 'Fotoalbum',
        self::VIEWMODE_PORTFOLIO => 'Portfolio',
    ];

    public string $name = '';
    public string $notes = '';
    public ?int $categoryId = null;
    public bool $showBreadcrumbs = false;
    public bool $hideFromOverview = false;
    public int $viewMode = self::VIEWMODE_REGULAR;

    public static function create(string $naam, string $notities = "", bool $showBreadcrumbs = false)
    {
        if ($naam == '')
            throw new Exception('Empty photo album name!');

        $id = DBConnection::doQuery('INSERT INTO photoalbums(`name`,`notes`,`showBreadcrumbs`) VALUES (?,?,?);', [$naam, $notities,(int)$showBreadcrumbs]);
        if ($id !== false)
        {
            Util::createDir(__DIR__ . "/../../fotoalbums/${id}");
            Util::createDir(__DIR__ . "/../../fotoalbums/${id}thumbnails");
        }

        return $id;
    }

    public static function edit(int $id, string $naam = null, $notities = null, bool $showBreadcrumbs = null)
    {
        if ($naam !== null)
        {
            DBConnection::doQueryAndFetchOne('UPDATE photoalbums SET `name`=? WHERE id=?', [$naam, $id]);
        }
        if ($notities !== null)
        {
            DBConnection::doQueryAndFetchOne('UPDATE photoalbums SET `notes`=? WHERE id=?', [$notities, $id]);
        }
        if ($showBreadcrumbs !== null)
        {
            DBConnection::doQueryAndFetchOne('UPDATE photoalbums SET `showBreadcrumbs`=? WHERE id=?', [(int)$showBreadcrumbs, $id]);
        }
    }

    public function getFriendlyUrl()
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
            $ret = array_values(array_filter($dirArray, function($value) {
                return substr($value, 0, 1) != '.';
            }));
        }

        return $ret;
    }

    public function getLinkPrefix()
    {
        return 'fotoalbums/' . $this->id . '/';
    }

    public function getThumbnailPrefix()
    {
        return 'fotoalbums/' . $this->id . 'thumbnails/';
    }
}