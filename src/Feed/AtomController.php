<?php
declare(strict_types=1);

namespace Cyndaron\Feed;

use Cyndaron\Category\CategoryRepository;
use Cyndaron\Error\ErrorPage;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Url\UrlService;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\BuiltinSetting;
use Cyndaron\Util\Setting;
use Cyndaron\View\Template\TemplateRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AtomController
{
    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly TemplateRenderer $templateRenderer
    ) {
    }

    #[RouteAttribute('category', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function category(QueryBits $queryBits, Request $request, UrlService $urlService, CategoryRepository $categoryRepository): Response
    {
        $categoryId = $queryBits->getInt(2);
        $category = $categoryRepository->fetchById($categoryId);
        if ($category === null)
        {
            return $this->pageRenderer->renderErrorResponse(new ErrorPage('Fout', 'Categorie niet gevonden!', Response::HTTP_NOT_FOUND));
        }

        /** @var \DateTimeInterface $savedDate */
        $savedDate = $category->created;
        $underlyingPages = $categoryRepository->getUnderlyingPages($category);
        foreach ($underlyingPages as $underlyingPage)
        {
            if ($underlyingPage->created->diff($savedDate)->invert)
            {
                $savedDate = $underlyingPage->created;
            }
        }

        $selfUri = $request->getSchemeAndHttpHost() . $request->getBaseUrl() . $request->getPathInfo();
        $siteName = Setting::get('siteName');

        $args = [
            'title' => "{$category->name} - {$siteName}",
            'organisation' => Setting::get(BuiltinSetting::ORGANISATION),
            'selfUri' => $selfUri,
            'category' => $category,
            'underlyingPages' => $underlyingPages,
            'baseUrl' => $request->getSchemeAndHttpHost(),
            'updated' => $savedDate,
            'urlService' => $urlService,
        ];

        $text = $this->templateRenderer->render('Feed/CategoryFeed', $args);
        return new Response($text, headers: ['Content-Type' => 'application/atom+xml']);
    }
}
