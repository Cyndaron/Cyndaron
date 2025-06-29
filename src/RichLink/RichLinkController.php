<?php
declare(strict_types=1);

namespace Cyndaron\RichLink;

use Cyndaron\Category\CategoryRepository;
use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class RichLinkController
{
    public function __construct(private readonly RichLinkRepository $richLinkRepository)
    {

    }

    #[RouteAttribute('edit', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function createOrEdit(RequestParameters $post, CategoryRepository $categoryRepository): JsonResponse
    {
        $id = $post->getInt('id');
        if ($id > 0)
        {
            $richlink = $this->richLinkRepository->fetchById($id);
            if ($richlink === null)
            {
                return new JsonResponse(['error' => 'RichLink does not exist!'], Response::HTTP_NOT_FOUND);
            }
        }
        else
        {
            $richlink = new RichLink();
        }

        $richlink->name = $post->getHTML('name');
        $richlink->url = $post->getUrl('url');
        $richlink->previewImage = $post->getUrl('previewImage');
        $richlink->blurb = $post->getHTML('blurb');
        $richlink->openInNewTab = $post->getBool('openInNewTab');

        try
        {
            $this->richLinkRepository->save($richlink);
        }
        catch (\Throwable)
        {
            return new JsonResponse(['error' => 'Could not save richlink!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        foreach ($categoryRepository->fetchAll() as $category)
        {
            $selected = $post->getBool('category-' . $category->id);
            if ($selected)
            {
                $this->richLinkRepository->linkToCategory($richlink, $category);
            }
            else
            {
                $this->richLinkRepository->unlinkFromCategory($richlink, $category);
            }
        }

        return new JsonResponse();
    }

    #[RouteAttribute('delete', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function delete(RequestParameters $post, GenericRepository $repository): JsonResponse
    {
        $id = $post->getInt('id');
        $richlink = $this->richLinkRepository->fetchById($id);
        if ($richlink === null)
        {
            return new JsonResponse(['error' => 'RichLink does not exist!'], Response::HTTP_NOT_FOUND);
        }

        $repository->delete($richlink);
        return new JsonResponse();
    }
}
