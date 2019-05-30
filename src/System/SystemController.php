<?php
declare (strict_types = 1);
namespace Cyndaron\System;

use Cyndaron\Controller;
use Cyndaron\Request;
use Cyndaron\Setting;
use Cyndaron\User\UserLevel;

class SystemController extends Controller
{
    protected $minLevelGet = UserLevel::ADMIN;

    protected function routeGet()
    {
        $currentPage = Request::getVar(1) ?: 'config';
        new SystemPage($currentPage);
    }

    protected function routePost()
    {
        Setting::set('websitenaam', Request::post('websitenaam'));
        Setting::set('websitelogo', Request::post('websitelogo'));
        Setting::set('ondertitel', Request::post('ondertitel'));
        Setting::set('favicon', Request::post('favicon'));
        Setting::set('achtergrondkleur', Request::post('achtergrondkleur'));
        Setting::set('menukleur', Request::post('menukleur'));
        Setting::set('menuachtergrond', Request::unsafePost('menuachtergrond'));
        Setting::set('artikelkleur', Request::post('artikelkleur'));
        Setting::set('accentColor', Request::post('accentColor'));
        Setting::set('standaardcategorie', Request::post('standaardcategorie'));
        Setting::set('menuthema', Request::post('menuthema'));
        Setting::set('frontPage', Request::post('frontPage'));

        new SystemPage('config');
    }
}