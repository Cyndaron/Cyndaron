<?php
declare (strict_types = 1);

namespace Cyndaron\Menu;

use Cyndaron\Controller;
use Cyndaron\User\UserLevel;

class MenuEditorController extends Controller
{
    protected int $minLevelGet = UserLevel::ADMIN;

    protected function routeGet()
    {
        new MenuEditorPage();
    }
}