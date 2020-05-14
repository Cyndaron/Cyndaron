<?php
declare (strict_types = 1);

namespace Cyndaron\Minecraft;

use Cyndaron\Controller;
use Cyndaron\Minecraft\Dynmap\DynmapProxy;
use Cyndaron\Request;
use Cyndaron\Response\JSONResponse;
use Cyndaron\User\UserLevel;
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
        $serverId = (int)Request::getVar(2);
        $server = Server::loadFromDatabase($serverId);
        if ($server === null)
        {
            return new JSONResponse(['error' => 'Server does not exist!'], Response::HTTP_NOT_FOUND);
        }

        $proxy = new DynmapProxy($server);

        return new Response(
            $proxy->getContents(),
            Response::HTTP_OK,
            ['content-type' => $proxy->getContentType()]);
    }

    public function members()
    {
        new LedenPagina();
    }

    public function skin()
    {
        new SkinRendererHandler();
    }

    public function status()
    {
        new StatusPagina();
    }
}