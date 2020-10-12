<?php
declare(strict_types=1);

namespace Cyndaron\RichLink;

use Cyndaron\Category\Category;
use Cyndaron\Routing\Controller;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class RichLinkController extends Controller
{
    public array $apiPostRoutes = [
        'edit' => ['level' => UserLevel::ADMIN, 'function' => 'createOrEdit'],
    ];

    public function createOrEdit(RequestParameters $post): JsonResponse
    {
        $id = $post->getInt('id');
        if ($id > 0)
        {
            $richlink = RichLink::loadFromDatabase($id);
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

        if (!$richlink->save())
        {
            return new JsonResponse(['error' => 'Could not save richlink!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        foreach (Category::fetchAll() as $category)
        {
            $selected = $post->getBool('category-' . $category->id);
            if ($selected)
            {
                $richlink->addCategory($category);
            }
            else
            {
                $richlink->removeCategory($category);
            }
        }

        return new JsonResponse();
    }
}
