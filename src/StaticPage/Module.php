<?php
namespace Cyndaron\StaticPage;

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
            'sub' => Datatype::fromArray([
                'singular' => 'Statische pagina',
                'plural' => 'Statische pagina\'s',
                'pageManagerTab' => PageManagerPage::class . '::showSubs',
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
            'sub' => StaticPageController::class,
        ];
    }

    public function url(array $linkParts): ?string
    {
        $model = StaticPageModel::loadFromDatabase((int)$linkParts[1]);
        return $model ? $model->name : null;
    }

    public function getList(): array
    {
        return DBConnection::doQueryAndFetchAll('SELECT CONCAT(\'/sub/\', id) AS link, CONCAT(\'Statische pag.: \', name) AS name FROM subs');
    }
}