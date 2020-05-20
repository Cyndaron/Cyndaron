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
        if ($serverId < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
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
        $page = new MembersPage();
        return new Response($page->render());
    }

    public function skin(): Response
    {
        $get = new RequestParameters($_GET);
        $format = $get->getSimpleString('format');
        $username = $get->getSimpleString('user');
        $member = Member::loadByUsername($username);
        $parameters = SkinRendererParameters::fromRequestParameters($get);

        $handler = new SkinRendererHandler($member, $format, $parameters);
        return $handler->draw();
    }

    public function status(): Response
    {
        $page = new StatusPagina();
        return new Response($page->render());
    }
}