<?php
declare(strict_types=1);

namespace Cyndaron\Minecraft;

use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\Controller;
use Cyndaron\Minecraft\Dynmap\DynmapProxy;
use Cyndaron\Minecraft\Skin\SkinRendererHandler;
use Cyndaron\Minecraft\Skin\SkinRendererParameters;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class MinecraftController extends Controller
{
    #[RouteAttribute('dynmapproxy', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function dynmapProxy(QueryBits $queryBits): Response
    {
        $serverId = $queryBits->getInt(2);
        if ($serverId < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $server = Server::fetchById($serverId);
        if ($server === null)
        {
            return new JsonResponse(['error' => 'Server does not exist!'], Response::HTTP_NOT_FOUND);
        }

        $proxy = new DynmapProxy($server, $queryBits);

        return new Response(
            $proxy->getContents(),
            Response::HTTP_OK,
            ['content-type' => $proxy->getContentType()]
        );
    }

    #[RouteAttribute('members', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function members(): Response
    {
        $page = new MembersPage();
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('skin', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function skin(): Response
    {
        $get = new RequestParameters($_GET);
        $format = $get->getSimpleString('format');
        $username = $get->getSimpleString('user');
        $member = Member::loadByUsername($username);
        if ($member === null)
        {
            return new Response('', Response::HTTP_NOT_FOUND);
        }
        $parameters = SkinRendererParameters::fromRequestParameters($get);

        $handler = new SkinRendererHandler($member, $format, $parameters);
        return $handler->draw($this->templateRenderer);
    }

    #[RouteAttribute('status', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function status(): Response
    {
        $page = new StatusPagina();
        return $this->pageRenderer->renderResponse($page);
    }
}
