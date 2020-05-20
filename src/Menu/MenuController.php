<?php
declare (strict_types = 1);

namespace Cyndaron\Menu;

use Cyndaron\Controller;
use Cyndaron\Error\DatabaseError;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends Controller
{
    protected array $apiPostRoutes = [
        'addItem' => ['level' => UserLevel::ADMIN, 'function' => 'addItem'],
        'editItem' => ['level' => UserLevel::ADMIN, 'function' => 'editItem'],
        'deleteItem' => ['level' => UserLevel::ADMIN, 'function' => 'deleteItem'],
    ];

    protected function addItem(RequestParameters $post): JsonResponse
    {
        $menuItem = new MenuItem();
        $menuItem->link = $post->getUrl('link');
        $menuItem->alias = $post->getUrl('alias');
        $menuItem->isDropdown = $post->getBool('isDropdown');
        $menuItem->isImage = $post->getBool('isImage');
        if ($post->hasVar('priority'))
        {
            $menuItem->priority = $post->getInt('priority');
        }

        if (!$menuItem->save())
        {
            throw new DatabaseError('Cannot add menu item!');
        }

        return new JsonResponse();
    }

    protected function editItem(RequestParameters $post): JsonResponse
    {
        $index = $this->queryBits->getNullableInt(2);
        $menuItem = new MenuItem($index);
        $menuItem->load();
        $menuItem->link = $post->getUrl('link');
        $menuItem->alias = $post->getUrl('alias');
        $menuItem->isDropdown = $post->getBool('isDropdown');
        $menuItem->isImage = $post->getBool('isImage');
        $menuItem->priority = $post->getInt('priority');

        if (!$menuItem->save())
        {
            throw new DatabaseError('Could not edit menu item!');
        }

        return new JsonResponse();
    }

    protected function deleteItem(): JsonResponse
    {
        $id = $this->queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $menuItem = new MenuItem($id);
        $menuItem->delete();

        return new JsonResponse();
    }
}