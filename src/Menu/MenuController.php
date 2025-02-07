<?php
declare(strict_types=1);

namespace Cyndaron\Menu;

use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\GenericRepository;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\DBAL\DatabaseError;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class MenuController
{
    #[RouteAttribute('addItem', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function addItem(RequestParameters $post, GenericRepository $repository, Connection $connection): JsonResponse
    {
        $menuItem = new MenuItem();
        $menuItem->link = $post->getUrl('link');
        $menuItem->alias = $post->getSimpleString('alias');
        $menuItem->isDropdown = $post->getBool('isDropdown');
        $menuItem->isImage = $post->getBool('isImage');
        if ($post->hasVar('priority'))
        {
            $menuItem->priority = $post->getInt('priority');
        }
        else
        {
            $currentHighPriority = (int)($connection->doQueryAndFetchOne('SELECT MAX(priority) FROM menu'));
            $menuItem->priority = $currentHighPriority + 1;
        }

        $repository->save($menuItem);
        return new JsonResponse();
    }

    #[RouteAttribute('editItem', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function editItem(QueryBits $queryBits, RequestParameters $post, GenericRepository $repository): JsonResponse
    {
        $index = $queryBits->getInt(2);
        $menuItem = MenuItem::fetchById($index);
        if ($menuItem === null)
        {
            throw new DatabaseError('Could not find menu item!');
        }

        $menuItem->link = $post->getUrl('link');
        $menuItem->alias = $post->getSimpleString('alias');
        $menuItem->isDropdown = $post->getBool('isDropdown');
        $menuItem->isImage = $post->getBool('isImage');
        $menuItem->priority = $post->getInt('priority');
        $repository->save($menuItem);

        return new JsonResponse();
    }

    #[RouteAttribute('deleteItem', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function deleteItem(QueryBits $queryBits, GenericRepository $repository): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $repository->deleteById(MenuItem::class, $id);

        return new JsonResponse();
    }
}
