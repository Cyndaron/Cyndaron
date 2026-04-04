<?php
declare(strict_types=1);

namespace Cyndaron\Category;

use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\Menu\MenuItem;
use Cyndaron\Menu\MenuItemRepository;
use Cyndaron\Photoalbum\Photoalbum;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\RichLink\RichLink;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\StaticPage\StaticPageModel;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use function explode;
use function get_class;
use function strpos;

final class CategoryAPIController
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    #[RouteAttribute('add', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function add(RequestParameters $post): JsonResponse
    {
        $return = [];
        $category = new Category(null);
        $category->name = $post->getHTML('name');
        $this->categoryRepository->save($category);

        return new JsonResponse($return);
    }

    #[RouteAttribute('addtomenu', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function addToMenu(QueryBits $queryBits, MenuItemRepository $menuItemRepository): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $return = [];
        $menuItem = new MenuItem();
        $menuItem->link = '/category/' . $id;
        $menuItemRepository->save($menuItem);

        return new JsonResponse($return);
    }

    #[RouteAttribute('delete', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function delete(QueryBits $queryBits, GenericRepository $repository): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $repository->deleteById(Category::class, $id);

        return new JsonResponse();
    }

    #[RouteAttribute('edit', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function edit(QueryBits $queryBits, RequestParameters $post): JsonResponse
    {
        $id = $queryBits->getInt(2);
        $category = $this->categoryRepository->fetchById($id);
        if ($category === null)
        {
            return new JsonResponse(['error' => 'Category does not exist!'], Response::HTTP_NOT_FOUND);
        }
        $category->name = $post->getHTML('name');
        $this->categoryRepository->save($category);

        return new JsonResponse();
    }

    #[RouteAttribute('changeOrder', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function changeOrder(QueryBits $queryBits, RequestParameters $post, Connection $db): JsonResponse
    {
        $categoryId = $queryBits->getInt(2);
        if ($categoryId < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $category = $this->categoryRepository->fetchById($categoryId);
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
                    $db->executeQuery('REPLACE INTO sub_categories(id, categoryId, priority) VALUES (?, ?, ?)', [$id, $categoryId, $priority]);
                    break;
                case 'category':
                    $db->executeQuery('REPLACE INTO category_categories(id, categoryId, priority) VALUES (?, ?, ?)', [$id, $categoryId, $priority]);
                    break;
                case 'photoalbum':
                    $db->executeQuery('REPLACE INTO photoalbum_categories(id, categoryId, priority) VALUES (?, ?, ?)', [$id, $categoryId, $priority]);
                    break;
                case 'richlink':
                    $db->executeQuery('REPLACE INTO richlink_category(id, categoryId, priority) VALUES (?, ?, ?)', [$id, $categoryId, $priority]);
                    break;
            }
        }

        return new JsonResponse();
    }

    #[RouteAttribute('underlyingPages', RequestMethod::GET, UserLevel::ANONYMOUS, isApiMethod: true)]
    public function underlyingPages(QueryBits $queryBits): JsonResponse
    {
        $categoryId = $queryBits->getInt(2);
        if ($categoryId < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $category = $this->categoryRepository->fetchById($categoryId);
        if ($category === null)
        {
            return new JsonResponse(['error' => 'Category does not exist!'], Response::HTTP_NOT_FOUND);
        }

        $underlyingPages = [];
        foreach ($this->categoryRepository->getUnderlyingPages($category, 'name') as $underlyingPage)
        {
            $entry = (array)$underlyingPage;
            $class = 'unknown';
            switch (get_class($underlyingPage))
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

            $entry['type'] = $class;

            $underlyingPages[] = $entry;
        }

        return new JsonResponse($underlyingPages);
    }
}
