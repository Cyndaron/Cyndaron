<?php
namespace Cyndaron\Category;

use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\Model;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Linkable;
use Cyndaron\Module\Routes;
use Cyndaron\Module\UrlProvider;
use Cyndaron\Url\Url;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\User\User;
use Cyndaron\User\UserRepository;
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
            'category' => new Datatype(
                singular: 'Categorie',
                plural: 'Categorieën',
                editorPage: EditorPage::class,
                editorSave: EditorSave::class,
                pageManagerTab: self::pageManagerTab(...),
                pageManagerJS: '/src/Category/js/PageManagerTab.js',
                class: Category::class,
                modelToUrl: function(Category $category)
                { return new Url("/category/{$category->id}"); },
            ),
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

    public function nameFromUrl(array $linkParts): string|null
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

    public static function pageManagerTab(User $currentUser, TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler, UserRepository $userRepository, CategoryRepository $categoryRepository): string
    {
        $templateVars = [
            'categories' => $categoryRepository->fetchAllAndSortByName(),
            'currentUser' => $currentUser,
            'userRepository' => $userRepository,
            'tokenAdd' => $tokenHandler->get('category', 'add'),
            'tokenDelete' => $tokenHandler->get('category', 'delete'),
            'tokenAddToMenu' => $tokenHandler->get('category', 'addtomenu'),
            'tokenChangeOrder' => $tokenHandler->get('category', 'changeOrder'),
        ];
        return $templateRenderer->render('Category/PageManagerTab', $templateVars);
    }
}
