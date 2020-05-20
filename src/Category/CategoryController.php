<?php
declare (strict_types = 1);

namespace Cyndaron\Category;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Menu\MenuItem;
use Cyndaron\Page;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    protected array $apiPostRoutes = [
        'add' => ['level' => UserLevel::ADMIN, 'function' => 'add'],
        'addtomenu' => ['level' => UserLevel::ADMIN, 'function' => 'addToMenu'],
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
        if ($result === false) {
            $return = DBConnection::errorInfo();
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
            $return = DBConnection::errorInfo();
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

}