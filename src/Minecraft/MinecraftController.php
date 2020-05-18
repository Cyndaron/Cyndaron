<?php
declare (strict_types = 1);

namespace Cyndaron\Minecraft;

use Cyndaron\Controller;
use Cyndaron\Minecraft\Dynmap\DynmapProxy;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class MinecraftController extends Controller
{
    public array $getRoutes = [
        'dynmapproxy' => ['level' => UserLevel::ANONYMOUS, 'function' => 'dynmapProxy'],
        'members' => ['level' => UserLevel::ANONYMOUS, 'function' => 'members'],
        'skin' => ['level' => UserLevel::ANONYMOUS, 'function' => 'skin'],
        'status' => ['level' => UserLevel::ANONYMOUS, 'function' => 'status'],
    ];

    public function dynmapProxy(): Response
    {
        $serverId = $this->queryBits->getInt(2);
        $server = Server::loadFromDatabase($serverId);
        if ($server === null)
        {
            return new JsonResponse(['error' => 'Server does not exist!'], Response::HTTP_NOT_FOUND);
        }

        $proxy = new DynmapProxy($server, $this->queryBits);

        return new Response(
            $proxy->getContents(),
            Response::HTTP_OK,
            ['content-type' => $proxy->getContentType()]);
    }

    public function members(): Response
    {
        $page = new LedenPagina();
        return new Response($page->render());
    }

    public function skin(): Response
    {
        ob_start();
        $get = new RequestParameters($_GET);
        new SkinRendererHandler($get);
        return new Response(ob_get_clean());
    }

    public function status(): Response
    {
        $page = new StatusPagina();
        return new Response($page->render());
    }
}