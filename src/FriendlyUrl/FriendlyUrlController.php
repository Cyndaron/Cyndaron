<?php
declare(strict_types=1);

namespace Cyndaron\FriendlyUrl;

use Cyndaron\Controller;
use Cyndaron\Menu\MenuItem;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Url;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FriendlyUrlController extends Controller
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

    public function addToMenu(): JsonResponse
    {
        $name = $this->queryBits->getString(3);
        if ($name === '')
        {
            return new JsonResponse(['error' => 'No link specified!'], Response::HTTP_BAD_REQUEST);
        }
        $menuItem = new MenuItem();
        $menuItem->link = '/' . $name;
        $menuItem->save();

        return new JsonResponse();
    }

    public function delete(): JsonResponse
    {
        $name = $this->queryBits->getString(2);
        if ($name === '')
        {
            return new JsonResponse(['error' => 'No link specified!'], Response::HTTP_BAD_REQUEST);
        }

        Url::deleteFriendlyUrl($name);
        return new JsonResponse();
    }
}
