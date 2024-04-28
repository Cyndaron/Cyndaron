<?php
declare(strict_types=1);

namespace Cyndaron\Category;

use Cyndaron\DBAL\Connection;
use Cyndaron\Error\ErrorPage;
use Cyndaron\Menu\MenuItem;
use Cyndaron\Page\SimplePage;
use Cyndaron\Photoalbum\Photoalbum;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\RichLink\RichLink;
use Cyndaron\Routing\Controller;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\StaticPage\StaticPageModel;
use Cyndaron\Url\UrlService;
use Cyndaron\User\UserLevel;
use Cyndaron\View\Renderer\TextRenderer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use function explode;
use function get_class;
use function strpos;

final class CategoryController extends Controller
{
    #[RouteAttribute('', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function view(QueryBits $queryBits, TextRenderer $textRenderer, UrlService $urlService): Response
    {
        $id = $queryBits->getString(1);

        if ($id === '0' || $id === 'fotoboeken')
        {
            $page = new PhotoalbumIndexPage($urlService);
            return $this->pageRenderer->renderResponse($page);
        }
        if ($id === 'tag')
        {
            $tag = $queryBits->getString(2);
            if ($tag === '')
            {
                $page = new SimplePage('Foute aanvraag', 'Lege tag ontvangen.');
                return $this->pageRenderer->renderResponse($page, status: Response::HTTP_BAD_REQUEST);
            }
            $page = new TagIndexPage($urlService, $tag);
            return $this->pageRenderer->renderResponse($page);
        }
        if ($id === '' || $id < 0)
        {
            $page = new SimplePage('Foute aanvraag', 'Incorrecte parameter ontvangen.');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_BAD_REQUEST);
        }

        $category = Category::fetchById((int)$id);
        if ($category === null)
        {
            return $this->pageRenderer->renderErrorResponse(new ErrorPage('Fout', 'Categorie niet gevonden!', Response::HTTP_NOT_FOUND));
        }

        $page = new CategoryIndexPage($urlService, $category, $textRenderer);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('add', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function add(RequestParameters $post): JsonResponse
    {
        $return = [];
        $category = new Category(null);
        $category->name = $post->getHTML('name');
        $result = $category->save();
        if (!$result)
        {
            return new JsonResponse(['error' => 'Could not save category!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse($return);
    }

    #[RouteAttribute('addtomenu', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function addToMenu(QueryBits $queryBits): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $return = [];
        $menuItem = new MenuItem();
        $menuItem->link = '/category/' . $id;
        $result = $menuItem->save();
        if (!$result)
        {
            return new JsonResponse(['error' => 'Could not save menu item!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse($return);
    }

    #[RouteAttribute('delete', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function delete(QueryBits $queryBits): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $category = new Category($id);
        $category->delete();

        return new JsonResponse();
    }

    #[RouteAttribute('edit', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function edit(QueryBits $queryBits, RequestParameters $post): JsonResponse
    {
        $id = $queryBits->getInt(2);
        $category = Category::fetchById($id);
        if ($category === null)
        {
            return new JsonResponse(['error' => 'Category does not exist!'], Response::HTTP_NOT_FOUND);
        }
        $category->name = $post->getHTML('name');
        $category->save();

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
        $category = Category::fetchById($categoryId);
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
        $category = Category::fetchById($categoryId);
        if ($category === null)
        {
            return new JsonResponse(['error' => 'Category does not exist!'], Response::HTTP_NOT_FOUND);
        }

        $underlyingPages = [];
        foreach ($category->getUnderlyingPages('name') as $underlyingPage)
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
