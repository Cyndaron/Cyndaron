<?php
namespace Cyndaron\Minecraft;

use Cyndaron\Page;

class StatusPagina extends Page
{
    public function __construct()
    {
        parent::__construct('Status en landkaart');

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