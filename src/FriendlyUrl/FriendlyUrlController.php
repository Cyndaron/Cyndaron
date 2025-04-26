<?php
declare(strict_types=1);

namespace Cyndaron\FriendlyUrl;

use Cyndaron\Menu\MenuItem;
use Cyndaron\Menu\MenuItemRepository;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Url\Url;
use Cyndaron\Url\UrlService;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class FriendlyUrlController
{
    public function __construct(
        private readonly FriendlyUrlRepository $friendlyUrlRepository,
    ) {
    }

    #[RouteAttribute('add', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function add(RequestParameters $post, UrlService $urlService): JsonResponse
    {
        $name = $post->getUrl('name');
        $target = new Url($post->getUrl('target'));
        $urlService->createFriendlyUrl($target, $name);

        return new JsonResponse();
    }

    #[RouteAttribute('addtomenu', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function addToMenu(QueryBits $queryBits, MenuItemRepository $menuItemRepository): JsonResponse
    {
        $id = $queryBits->getInt(2);
        $entry = $this->friendlyUrlRepository->fetchById($id);
        if ($entry === null)
        {
            return new JsonResponse(['error' => 'No link specified!'], Response::HTTP_BAD_REQUEST);
        }
        $menuItem = new MenuItem();
        $menuItem->link = '/' . $entry->name;
        $menuItemRepository->save($menuItem);

        return new JsonResponse();
    }

    #[RouteAttribute('delete', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function delete(QueryBits $queryBits): JsonResponse
    {
        $id = $queryBits->getInt(2);
        $this->friendlyUrlRepository->deleteById($id);
        return new JsonResponse();
    }
}
