<?php
namespace Cyndaron\Minecraft;

use Cyndaron\Page\Page;
use function array_walk;

final class StatusPagina extends Page
{
    public function __construct()
    {
        $this->title = 'Status en landkaart';
        $this->addCss('/src/Minecraft/css/statuspage.min.css');

        $servers = Server::fetchAll([], [], 'ORDER BY name');
        array_walk($servers, static function(Server $server)
        {
            $server->retrieveInfo();
        });

        $this->addTemplateVars([
            'servers' => $servers,
        ]);
    }
}
