<?php
declare (strict_types = 1);
namespace Cyndaron\System;

use Cyndaron\Controller;
use Cyndaron\Request;
use Cyndaron\Setting;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class SystemController extends Controller
{
    protected int $minLevelGet = UserLevel::ADMIN;

    protected function routeGet(): Response
    {
        $currentPage = $this->queryBits->get(1, 'config');
        $page = new SystemPage($currentPage);
        return new Response($page->render());
    }

    protected function routePost(): Response
    {
        Setting::set('siteName', Request::post('siteName'));
        Setting::set('organisation', Request::post('organisation'));
        Setting::set('logo', Request::post('logo'));
        Setting::set('subTitle', Request::post('subTitle'));
        Setting::set('favicon', Request::post('favicon'));
        Setting::set('backgroundColor', Request::post('backgroundColor'));
        Setting::set('menuColor', Request::post('menuColor'));
        Setting::set('menuBackground', Request::unsafePost('menuBackground'));
        Setting::set('articleColor', Request::post('articleColor'));
        Setting::set('accentColor', Request::post('accentColor'));
        Setting::set('defaultCategory', Request::post('defaultCategory'));
        Setting::set('menuTheme', Request::post('menuTheme'));
        Setting::set('frontPage', Request::post('frontPage'));
        Setting::set('frontPageIsJumbo', Request::post('frontPageIsJumbo'));

        // Redirect to GET
        return new RedirectResponse('/system/config');
    }
}