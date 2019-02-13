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

    public function routeGet()
    {
        $currentPage = Request::getVar(1) ?: 'config';
        new SystemPage($currentPage);
    }

    public function routePost()
    {
        Setting::set('websitenaam', Request::geefPostVeilig('websitenaam'));
        Setting::set('websitelogo', Request::geefPostVeilig('websitelogo'));
        Setting::set('ondertitel', Request::geefPostVeilig('ondertitel'));
        Setting::set('favicon', Request::geefPostVeilig('favicon'));
        Setting::set('achtergrondkleur', Request::geefPostVeilig('achtergrondkleur'));
        Setting::set('menukleur', Request::geefPostVeilig('menukleur'));
        Setting::set('menuachtergrond', Request::geefPostOnveilig('menuachtergrond'));
        Setting::set('artikelkleur', Request::geefPostVeilig('artikelkleur'));
        Setting::set('standaardcategorie', Request::geefPostVeilig('standaardcategorie'));
        Setting::set('menuthema', Request::geefPostVeilig('menuthema'));

        new SystemPage('config');
    }
}