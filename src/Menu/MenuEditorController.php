<?php
declare (strict_types = 1);

namespace Cyndaron\Menu;

use Cyndaron\Controller;
use Cyndaron\User\UserLevel;

class MenuEditorController extends Controller
{
    protected $minLevelGet = UserLevel::ADMIN;

    public function routeGet()
    {
        new MenuEditorPage();
    }
}