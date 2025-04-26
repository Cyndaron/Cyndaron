<?php
declare(strict_types=1);
namespace Cyndaron\System;

use Cyndaron\Category\CategoryRepository;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Translation\Translator;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\SettingsRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

final class SystemController
{
    public function __construct(
        private readonly PageRenderer $pageRenderer,
    ) {
    }

    #[RouteAttribute('', RequestMethod::GET, UserLevel::ADMIN)]
    public function routeGet(QueryBits $queryBits, Translator $t, CategoryRepository $categoryRepository, SettingsRepository $sr): Response
    {
        $currentPage = $queryBits->getString(1, 'config');
        $page = new SystemPage($currentPage, $t, $categoryRepository, $sr);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('', RequestMethod::POST, UserLevel::ADMIN)]
    public function routePost(RequestParameters $post, SettingsRepository $sr): Response
    {
        $sr->set('siteName', $post->getHTML('siteName'));
        $sr->set('organisation', $post->getHTML('organisation'));
        $sr->set('shortCode', $post->getHTML('shortCode'));
        $sr->set('logo', $post->getFilenameWithDirectory('logo'));
        $sr->set('subTitle', $post->getHTML('subTitle'));
        $sr->set('favicon', $post->getFilenameWithDirectory('favicon'));
        $sr->set('backgroundColor', $post->getColor('backgroundColor'));
        $sr->set('menuColor', $post->getColor('menuColor'));
        $sr->set('menuBackground', $post->getFilenameWithDirectory('menuBackground'));
        $sr->set('articleColor', $post->getColor('articleColor'));
        $sr->set('accentColor', $post->getColor('accentColor'));
        $sr->set('defaultCategory', (string)$post->getInt('defaultCategory'));
        $sr->set('menuTheme', $post->getSimpleString('menuTheme'));
        $sr->set('frontPage', $post->getUrl('frontPage'));
        $sr->set('frontPageIsJumbo', (string)(int)$post->getBool('frontPageIsJumbo'));
        $sr->set('mail_logRecipient', $post->getEmail('mail_logRecipient'));
        $sr->buildCache();

        // Redirect to GET
        return new RedirectResponse('/system/config');
    }
}
