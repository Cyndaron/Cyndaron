<?php
declare(strict_types=1);

namespace Cyndaron\FriendlyUrl;

use Cyndaron\Request\QueryBits;
use Cyndaron\Routing\Controller;
use Cyndaron\Menu\MenuItem;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Url;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class FriendlyUrlController extends Controller
{
    protected array $apiPostRoutes = [
        'add' => ['level' => UserLevel::ADMIN, 'function' => 'add'],
        'addtomenu' => ['level' => UserLevel::ADMIN, 'function' => 'addToMenu'],
        'delete' => ['level' => UserLevel::ADMIN, 'function' => 'delete'],
    ];

    public function add(RequestParameters $post): JsonResponse
    {
        $name = $post->getUrl('name');
        $target = new Url($post->getUrl('target'));
        $target->createFriendly($name);

        return new JsonResponse();
    }

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

    public function delete(QueryBits $queryBits): JsonResponse
    {
        $id = $queryBits->getInt(2);
        FriendlyUrl::deleteById($id);
        return new JsonResponse();
    }
}
