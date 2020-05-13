<?php
declare (strict_types = 1);

namespace Cyndaron\Minecraft;

use Cyndaron\Controller;
use Cyndaron\Minecraft\Dynmap\DynmapProxy;
use Cyndaron\Request;

class MinecraftController extends Controller
{
    protected function routeGet()
    {
        switch ($this->action)
        {
            case 'dynmapproxy':
                $serverId = (int)Request::getVar(2);
                $server = Server::loadFromDatabase($serverId);
                if ($server === null)
                {
                    $this->send404();
                    return;
                }
                new DynmapProxy($server);
                break;
            case 'members':
                new LedenPagina();
                break;
            case 'skin':
                new SkinRendererHandler();
                break;
            case 'status':
                new StatusPagina();
                break;
        }
    }
}