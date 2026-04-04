<?php
namespace Cyndaron\Category;

use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;
use Cyndaron\Module\UrlProvider;
use Cyndaron\Url\Url;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\User\User;
use Cyndaron\User\UserRepository;
use Cyndaron\View\Template\TemplateRenderer;

final class Module implements Datatypes, Routes, UrlProvider
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
            'category' => [
                CategoryController::class,
                CategoryIndexPage::class,
                TagIndexPage::class,
            ]
        ];
    }

    public function nameFromUrl(GenericRepository $genericRepository, array $linkParts): string|null
    {
        if ((int)$linkParts[1] === 0 || $linkParts[1] === 'fotoboeken')
        {
            return 'Fotoalbums';
        }

        $category = $genericRepository->fetchById(Category::class, (int)$linkParts[1]);
        return $category?->name;
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
