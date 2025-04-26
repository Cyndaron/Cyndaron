<?php
declare(strict_types=1);

namespace Cyndaron\Minecraft;

use Cyndaron\Minecraft\Dynmap\DynmapProxy;
use Cyndaron\Minecraft\Member\MemberRepository;
use Cyndaron\Minecraft\Member\MembersPage;
use Cyndaron\Minecraft\Server\ServerRepository;
use Cyndaron\Minecraft\Server\StatusPage;
use Cyndaron\Minecraft\Skin\SkinRendererHandler;
use Cyndaron\Minecraft\Skin\SkinRendererParameters;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Cyndaron\View\Template\TemplateRenderer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class MinecraftController
{
    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
        private readonly PageRenderer $pageRenderer,
    ) {
    }

    #[RouteAttribute('dynmapproxy', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function dynmapProxy(QueryBits $queryBits, ServerRepository $serverRepository): Response
    {
        $serverId = $queryBits->getInt(2);
        if ($serverId < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $server = $serverRepository->fetchById($serverId);
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
    public function members(MemberRepository $memberRepository): Response
    {
        $page = new MembersPage($memberRepository);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('skin', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function skin(Request $request, MemberRepository $memberRepository): Response
    {
        $get = new RequestParameters($request->query->all());
        $format = $get->getSimpleString('format');
        $username = $get->getSimpleString('user');
        $member = $memberRepository->loadByUsername($username);
        if ($member === null)
        {
            return new Response('', Response::HTTP_NOT_FOUND);
        }
        $parameters = SkinRendererParameters::fromRequestParameters($get);

        $handler = new SkinRendererHandler($member, $format, $parameters);
        return $handler->draw($this->templateRenderer);
    }

    #[RouteAttribute('status', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function status(ServerRepository $serverRepository): Response
    {
        $page = new StatusPage($serverRepository);
        return $this->pageRenderer->renderResponse($page);
    }
}
