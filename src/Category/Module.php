<?php
namespace Cyndaron\Category;

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
            'category' => Datatype::fromArray([
                'singular' => 'Categorie',
                'plural' => 'Categorieën',
                'pageManagerTab' => PageManagerPage::class . '::showCategories',
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
            'category' => CategoryController::class,
        ];
    }

    public function url(array $linkParts): ?string
    {
        if ($linkParts[1] == 0 || $linkParts[1] == 'fotoboeken')
        {
            return 'Fotoalbums';
        }
        else
        {
            $category = Category::loadFromDatabase((int)$linkParts[1]);
            return $category ? $category->name : null;
        }
    }

    public function getList(): array
    {
        return DBConnection::doQueryAndFetchAll('SELECT CONCAT(\'/category/\', id) AS link, CONCAT(\'Categorie: \', name) AS name FROM categories');
    }
}