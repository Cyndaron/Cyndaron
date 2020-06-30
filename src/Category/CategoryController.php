<?php
declare(strict_types=1);

namespace Cyndaron\Category;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Menu\MenuItem;
use Cyndaron\ModelWithCategory;
use Cyndaron\Page;
use Cyndaron\Photoalbum\Photoalbum;
use Cyndaron\Request\RequestParameters;
use Cyndaron\RichLink\RichLink;
use Cyndaron\StaticPage\StaticPageModel;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    protected array $apiGetRoutes = [
        'underlyingPages' => ['level' => UserLevel::ANONYMOUS, 'function' => 'underlyingPages'],
    ];

    protected array $apiPostRoutes = [
        'add' => ['level' => UserLevel::ADMIN, 'function' => 'add'],
        'addtomenu' => ['level' => UserLevel::ADMIN, 'function' => 'addToMenu'],
        'changeOrder' => ['level' => UserLevel::ADMIN, 'function' => 'changeOrder'],
        'delete' => ['level' => UserLevel::ADMIN, 'function' => 'delete'],
        'edit' => ['level' => UserLevel::ADMIN, 'function' => 'edit'],
    ];

    protected function routeGet(): Response
    {
        $id = $this->queryBits->getString(1);

        if ($id === '0' || $id === 'fotoboeken')
        {
            $page = new PhotoalbumIndexPage();
            return new Response($page->render());
        }
        if ($id === 'tag')
        {
            $tag = $this->queryBits->getString(2);
            if ($tag === '')
            {
                $page = new Page('Foute aanvraag', 'Lege tag ontvangen.');
                return new Response($page->render(), Response::HTTP_BAD_REQUEST);
            }
            $page = new TagIndexPage($tag);
            return new Response($page->render());
        }
        if ($id === '' || $id < 0)
        {
            $page = new Page('Foute aanvraag', 'Incorrecte parameter ontvangen.');
            return new Response($page->render(), Response::HTTP_BAD_REQUEST);
        }

        $category = Category::loadFromDatabase((int)$id);
        $page = new CategoryIndexPage($category);
        return new Response($page->render());
    }

    public function add(RequestParameters $post): JsonResponse
    {
        $return = [];
        $category = new Category(null);
        $category->name = $post->getHTML('name');
        $result = $category->save();
        if ($result === false)
        {
            return new JsonResponse(['error' => 'Could not save category!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse($return);
    }

    public function addToMenu(): JsonResponse
    {
        $id = $this->queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $return = [];
        $menuItem = new MenuItem();
        $menuItem->link = '/category/' . $id;
        $result = $menuItem->save();
        if ($result === false)
        {
            return new JsonResponse(['error' => 'Could not save menu item!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse($return);
    }

    public function delete(): JsonResponse
    {
        $id = $this->queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $category = new Category($id);
        $category->delete();

        return new JsonResponse();
    }

    public function edit(RequestParameters $post): JsonResponse
    {
        $id = $this->queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $category = new Category($id);
        $category->load();
        $category->name = $post->getHTML('name');
        $category->save();

        return new JsonResponse();
    }

    public function changeOrder(RequestParameters $post): JsonResponse
    {
        $categoryId = $this->queryBits->getInt(2);
        if ($categoryId < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $category = Category::loadFromDatabase($categoryId);
        if ($category === null)
        {
            return new JsonResponse(['error' => 'Category does not exist!'], Response::HTTP_NOT_FOUND);
        }

        foreach ($post->getKeys() as $fieldName)
        {
            if (strpos($fieldName, '-') === false)
            {
                continue;
            }

            [$type, $id] = explode('-', $fieldName);
            $priority = $post->getInt($fieldName);

            switch ($type)
            {
                case 'sub':
                    DBConnection::doQuery('REPLACE INTO sub_categories(id, categoryId, priority) VALUES (?, ?, ?)', [$id, $categoryId, $priority]);
                    break;
                case 'category':
                    DBConnection::doQuery('REPLACE INTO category_categories(id, categoryId, priority) VALUES (?, ?, ?)', [$id, $categoryId, $priority]);
                    break;
                case 'photoalbum':
                    DBConnection::doQuery('REPLACE INTO photoalbum_categories(id, categoryId, priority) VALUES (?, ?, ?)', [$id, $categoryId, $priority]);
                    break;
                case 'richlink':
                    DBConnection::doQuery('REPLACE INTO richlink_category(id, categoryId, priority) VALUES (?, ?, ?)', [$id, $categoryId, $priority]);
                    break;
            }
        }

        return new JsonResponse();
    }

    public function underlyingPages(): JsonResponse
    {
        $categoryId = $this->queryBits->getInt(2);
        if ($categoryId < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $category = Category::loadFromDatabase($categoryId);
        if ($category === null)
        {
            return new JsonResponse(['error' => 'Category does not exist!'], Response::HTTP_NOT_FOUND);
        }

        $underlyingPages = $category->getUnderlyingPages('name');
        array_walk($underlyingPages, static function(ModelWithCategory $model)
        {
            $class = 'unknown';
            switch (get_class($model))
            {
                case StaticPageModel::class:
                    $class = 'sub';
                    break;
                case Category::class:
                    $class = 'category';
                    break;
                case Photoalbum::class:
                    $class = 'photoalbum';
                    break;
                case RichLink::class:
                    $class = 'richlink';
                    break;
            }
            /** @noinspection PhpUndefinedFieldInspection */
            $model->type = $class;
        });

        return new JsonResponse($underlyingPages);
    }
}
