<?php
declare(strict_types=1);
namespace Cyndaron\System;

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
    public function routeGet(QueryBits $queryBits, Translator $t): Response
    {
        $currentPage = $queryBits->getString(1, 'config');
        $page = new SystemPage($currentPage, $t);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('', RequestMethod::POST, UserLevel::ADMIN)]
    public function routePost(RequestParameters $post, SettingsRepository $settings): Response
    {
        $settings->set('siteName', $post->getHTML('siteName'));
        $settings->set('organisation', $post->getHTML('organisation'));
        $settings->set('shortCode', $post->getHTML('shortCode'));
        $settings->set('logo', $post->getFilenameWithDirectory('logo'));
        $settings->set('subTitle', $post->getHTML('subTitle'));
        $settings->set('favicon', $post->getFilenameWithDirectory('favicon'));
        $settings->set('backgroundColor', $post->getColor('backgroundColor'));
        $settings->set('menuColor', $post->getColor('menuColor'));
        $settings->set('menuBackground', $post->getFilenameWithDirectory('menuBackground'));
        $settings->set('articleColor', $post->getColor('articleColor'));
        $settings->set('accentColor', $post->getColor('accentColor'));
        $settings->set('defaultCategory', (string)$post->getInt('defaultCategory'));
        $settings->set('menuTheme', $post->getSimpleString('menuTheme'));
        $settings->set('frontPage', $post->getUrl('frontPage'));
        $settings->set('frontPageIsJumbo', (string)(int)$post->getBool('frontPageIsJumbo'));
        $settings->set('mail_logRecipient', $post->getEmail('mail_logRecipient'));
        $settings->buildCache();

        // Redirect to GET
        return new RedirectResponse('/system/config');
    }
}
