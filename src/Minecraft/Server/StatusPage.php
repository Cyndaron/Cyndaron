<?php
namespace Cyndaron\Minecraft\Server;

use Cyndaron\Page\Page;
use function array_walk;

final class StatusPage extends Page
{
    public function __construct(ServerRepository $serverRepository)
    {
        $this->title = 'Status en landkaart';
        $this->addCss('/src/Minecraft/css/statuspage.min.css');

        $servers = $serverRepository->fetchAll([], [], 'ORDER BY name');
        array_walk($servers, static function(Server $server)
        {
            $server->retrieveInfo();
        });

        $this->addTemplateVars([
            'servers' => $servers,
        ]);
    }
}
