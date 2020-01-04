<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\DBConnection;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Linkable;
use Cyndaron\Module\Routes;
use Cyndaron\Module\UrlProvider;
use Cyndaron\PageManager\PageManagerPage;

class Module implements Datatypes, Routes, UrlProvider, Linkable
{
    /**
     * @inheritDoc
     */
    public function dataTypes(): array
    {
        return [
            'photo' => Datatype::fromArray([
                'singular' => 'Foto',
                'plural' => 'Foto\'s',
                'editorPage' => EditorPagePhoto::class,
                'editorSavePage' => EditorSavePagePhoto::class,
            ]),
            'photoalbum' => Datatype::fromArray([
                'singular' => 'Fotoalbum',
                'plural' => 'Fotoalbums',
                'pageManagerTab' => PageManagerPage::class . '::showPhotoalbums',
                'editorPage' => EditorPage::class,
                'editorSavePage' => EditorSavePage::class,
            ]),
        ];
    }

    /**
     * @inheritDoc
     */
    public function routes(): array
    {
        return [
            'photoalbum' => PhotoalbumController::class,
        ];
    }

    public function url(array $linkParts): ?string
    {
        $album = Photoalbum::loadFromDatabase((int)$linkParts[1]);
        return $album ? $album->name : null;
    }

    public function getList(): array
    {
        return DBConnection::doQueryAndFetchAll('SELECT CONCAT(\'/photoalbum/\', id) AS link, CONCAT(\'Fotoalbum: \', name) AS name FROM photoalbums');
    }
}