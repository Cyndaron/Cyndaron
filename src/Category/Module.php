<?php
namespace Cyndaron\Category;

use Cyndaron\DBAL\Connection;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Linkable;
use Cyndaron\Module\Routes;
use Cyndaron\Module\UrlProvider;
use Cyndaron\User\User;
use Cyndaron\Util\Link;
use Cyndaron\View\Template\TemplateRenderer;
use function array_map;

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
                'editorSave' => EditorSave::class,
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

    public function url(array $linkParts): string|null
    {
        if ((int)$linkParts[1] === 0 || $linkParts[1] === 'fotoboeken')
        {
            return 'Fotoalbums';
        }

        $category = Category::fetchById((int)$linkParts[1]);
        return $category !== null ? $category->name : null;
    }

    public function getList(Connection $connection): array
    {
        /** @var list<array{name: string, link: string}> $list */
        $list = $connection->doQueryAndFetchAll('SELECT CONCAT(\'/category/\', id) AS link, CONCAT(\'Categorie: \', name) AS name FROM categories');
        return array_map(static function(array $item)
        {
            return Link::fromArray($item);
        }, $list);
    }

    public static function pageManagerTab(User $currentUser, TemplateRenderer $templateRenderer): string
    {
        $templateVars = [
            'categories' => Category::fetchAll([], [], 'ORDER BY name'),
            'currentUser' => $currentUser,
        ];
        return $templateRenderer->render('Category/PageManagerTab', $templateVars);
    }
}
