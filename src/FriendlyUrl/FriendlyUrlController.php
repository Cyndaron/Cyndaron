<?php
declare (strict_types = 1);

namespace Cyndaron\FriendlyUrl;

use Cyndaron\Controller;
use Cyndaron\Menu\MenuItem;
use Cyndaron\Request;
use Cyndaron\Url;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;

class FriendlyUrlController extends Controller
{
    protected array $apiPostRoutes = [
        'add' => ['level' => UserLevel::ADMIN, 'function' => 'add'],
        'addtomenu' => ['level' => UserLevel::ADMIN, 'function' => 'addToMenu'],
        'delete' => ['level' => UserLevel::ADMIN, 'function' => 'delete'],
    ];

    public function add(): JsonResponse
    {
        $name = Request::post('name');
        $target = new Url(Request::post('target'));
        $target->createFriendly($name);

        return new JsonResponse();
    }

    public function addToMenu(): JsonResponse
    {
        $name = $this->queryBits->get(3);
        $menuItem = new MenuItem();
        $menuItem->link = '/' . $name;
        $menuItem->save();

        return new JsonResponse();
    }

    public function delete(): JsonResponse
    {
        $name = $this->queryBits->get(2);
        Url::deleteFriendlyUrl($name);

        return new JsonResponse();
    }
}