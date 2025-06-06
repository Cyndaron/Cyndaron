<?php
declare(strict_types=1);

namespace Cyndaron\StaticPage;

use Cyndaron\DBAL\Connection;
use Cyndaron\Menu\MenuItem;
use Cyndaron\Menu\MenuItemRepository;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\Error\IncompleteData;
use Cyndaron\View\Renderer\TextRenderer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use function strtolower;

final class StaticPageController
{
    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly StaticPageRepository $staticPageRepository,
    ) {
    }

    #[RouteAttribute('', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function routeGet(QueryBits $queryBits, TextRenderer $textRenderer, Connection $connection): Response
    {
        $id = $queryBits->getInt(1);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $model = $this->staticPageRepository->fetchById($id);
        if ($model === null)
        {
            $page = new SimplePage('Fout', 'Statische pagina niet gevonden.');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_NOT_FOUND);
        }
        $page = new StaticPage($model, $this->staticPageRepository, $connection, $textRenderer);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('addtomenu', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function addToMenu(QueryBits $queryBits, MenuItemRepository $menuItemRepository): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $menuItem = new MenuItem();
        $menuItem->link = '/sub/' . $id;
        $menuItemRepository->save($menuItem);
        return new JsonResponse();
    }

    #[RouteAttribute('delete', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function delete(QueryBits $queryBits, StaticPageRepository $repository): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $repository->deleteById($id);
        return new JsonResponse();
    }

    #[RouteAttribute('react', RequestMethod::POST, UserLevel::ANONYMOUS)]
    public function react(QueryBits $queryBits, RequestParameters $post, Connection $connection): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $model = $this->staticPageRepository->fetchById($id);
        if ($model === null)
        {
            $page = new SimplePage('Fout', 'Statische pagina niet gevonden.');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_NOT_FOUND);
        }

        $author = $post->getHTML('author');
        $reactie = $post->getHTML('reactie');
        $antispam = strtolower($post->getAlphaNum('antispam'));
        $this->saveReaction($model, $connection, $author, $reactie, $antispam);

        return new RedirectResponse("/sub/$id");
    }

    private function saveReaction(StaticPageModel $staticPage, Connection $connection, string $author, string $reactie, string $antispam): bool
    {
        if ($staticPage->id === null)
        {
            throw new IncompleteData('No ID!');
        }
        if ($staticPage->enableComments && $author && $reactie && ($antispam === 'acht' || $antispam === '8'))
        {
            $prep = $connection->prepare('INSERT INTO sub_replies(subId, author, text) VALUES (?, ?, ?)');
            $prep->execute([$staticPage->id, $author, $reactie]);
            return true;
        }
        return false;
    }
}
