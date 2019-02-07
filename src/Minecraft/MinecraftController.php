<?php
declare(strict_types = 1);

namespace Cyndaron\Minecraft;

use Cyndaron\Controller;
use Cyndaron\Minecraft\Dynmap\DynmapProxy;

class MinecraftController extends Controller
{
    public function routeGet()
    {
        switch ($this->action)
        {
            case 'dynmapproxy':
                new DynmapProxy();
                break;
        }
    }
}