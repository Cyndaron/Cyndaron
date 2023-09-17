<?php
namespace Cyndaron\Category;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\InternalLink;
use Cyndaron\Module\Linkable;
use Cyndaron\Module\Routes;
use Cyndaron\Module\UrlProvider;
use Cyndaron\View\Template\Template;

final class Module implements Datatypes, Routes, UrlProvider, Linkable
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
                'pageManagerTab' => self::class . '::pageManagerTab',
                'pageManagerJS' => '/src/Category/js/PageManagerTab.js',
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
        if ((int)$linkParts[1] === 0 || $linkParts[1] === 'fotoboeken')
        {
            return 'Fotoalbums';
        }

        $category = Category::fetchById((int)$linkParts[1]);
        return $category !== null ? $category->name : null;
    }

    public function getList(): array
    {
        /** @var list<array{name: string, link: string}> $list */
        $list = DBConnection::getPDO()->doQueryAndFetchAll('SELECT CONCAT(\'/category/\', id) AS link, CONCAT(\'Categorie: \', name) AS name FROM categories');
        return array_map(static function (array $item)
        {
            return InternalLink::fromArray($item);
        }, $list);
    }

    public static function pageManagerTab(): string
    {
        $templateVars = ['categories' => Category::fetchAll([], [], 'ORDER BY name')];
        return (new Template())->render('Category/PageManagerTab', $templateVars);
    }
}
