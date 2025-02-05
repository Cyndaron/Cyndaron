<?php
declare(strict_types=1);

namespace Cyndaron\FriendlyUrl;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\Menu\MenuItem;
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
    #[RouteAttribute('add', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function add(RequestParameters $post, UrlService $urlService): JsonResponse
    {
        $name = $post->getUrl('name');
        $target = new Url($post->getUrl('target'));
        $urlService->createFriendlyUrl($target, $name);

        return new JsonResponse();
    }

    #[RouteAttribute('addtomenu', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function addToMenu(QueryBits $queryBits): JsonResponse
    {
        $id = $queryBits->getInt(2);
        $entry = FriendlyUrl::fetchById($id);
        if ($entry === null)
        {
            return new JsonResponse(['error' => 'No link specified!'], Response::HTTP_BAD_REQUEST);
        }
        $menuItem = new MenuItem();
        $menuItem->link = '/' . $entry->name;
        $menuItem->save();

        return new JsonResponse();
    }

    #[RouteAttribute('delete', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function delete(QueryBits $queryBits, GenericRepository $repository): JsonResponse
    {
        $id = $queryBits->getInt(2);
        $repository->deleteById(FriendlyUrl::class, $id);
        return new JsonResponse();
    }
}
