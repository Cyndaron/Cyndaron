<?php
declare(strict_types=1);
namespace Cyndaron\System;

use Cyndaron\Request\QueryBits;
use Cyndaron\Routing\Controller;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Util\Setting;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

final class SystemController extends Controller
{
    protected array $getRoutes = [
        '' => ['level' => UserLevel::ADMIN, 'function' => 'routeGet'],
    ];
    protected array $postRoutes = [
        '' => ['level' => UserLevel::ADMIN, 'function' => 'routePost'],
    ];

    protected function routeGet(QueryBits $queryBits): Response
    {
        $currentPage = $queryBits->getString(1, 'config');
        $page = new SystemPage($currentPage);
        return new Response($page->render());
    }

    protected function routePost(RequestParameters $post): Response
    {
        Setting::set('siteName', $post->getHTML('siteName'));
        Setting::set('organisation', $post->getHTML('organisation'));
        Setting::set('logo', $post->getFilenameWithDirectory('logo'));
        Setting::set('subTitle', $post->getHTML('subTitle'));
        Setting::set('favicon', $post->getFilenameWithDirectory('favicon'));
        Setting::set('backgroundColor', $post->getColor('backgroundColor'));
        Setting::set('menuColor', $post->getColor('menuColor'));
        Setting::set('menuBackground', $post->getFilenameWithDirectory('menuBackground'));
        Setting::set('articleColor', $post->getColor('articleColor'));
        Setting::set('accentColor', $post->getColor('accentColor'));
        Setting::set('defaultCategory', (string)$post->getInt('defaultCategory'));
        Setting::set('menuTheme', $post->getSimpleString('menuTheme'));
        Setting::set('frontPage', $post->getUrl('frontPage'));
        Setting::set('frontPageIsJumbo', (string)(int)$post->getBool('frontPageIsJumbo'));
        Setting::buildCache();

        // Redirect to GET
        return new RedirectResponse('/system/config');
    }
}
