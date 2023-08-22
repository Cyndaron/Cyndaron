<?php
declare(strict_types=1);

namespace Cyndaron\Feed;

use Cyndaron\Category\Category;
use Cyndaron\Error\ErrorPageResponse;
use Cyndaron\Request\QueryBits;
use Cyndaron\Routing\Controller;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\Setting;
use Cyndaron\Util\Util;
use Cyndaron\View\Template\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AtomController extends Controller
{
    protected array $getRoutes = [
        'category' => ['level' => UserLevel::ANONYMOUS, 'function' => 'category'],
    ];

    protected function category(QueryBits $queryBits, Request $request): Response
    {
        $categoryId = $queryBits->getInt(2);
        $category = Category::fetchById($categoryId);
        if ($category === null)
        {
            return new ErrorPageResponse('Fout', 'Categorie niet gevonden!', Response::HTTP_NOT_FOUND);
        }

        /** @var \DateTimeInterface|null $savedDate */
        $savedDate = null;
        $underlyingPages = $category->getUnderlyingPages();
        foreach ($underlyingPages as $underlyingPage)
        {
            if ($savedDate === null || $underlyingPage->created->diff($savedDate)->invert)
            {
                $savedDate = $underlyingPage->created;
            }
        }

        $selfUri = $request->getSchemeAndHttpHost() . $request->getBaseUrl() . $request->getPathInfo();

        $args = [
            'title' => "Atomfeed voor categorie {$category->name}",
            'organisation' => Setting::get(Setting::ORGANISATION),
            'selfUri' => $selfUri,
            'category' => $category,
            'underlyingPages' => $underlyingPages,
            'domain' => Util::getDomain(),
            'updated' => $savedDate,
        ];

        $template = new Template();
        $text = $template->render('Feed/CategoryFeed', $args);
        return new Response($text, headers: ['Content-Type' => 'application/atom+xml']);
    }
}
