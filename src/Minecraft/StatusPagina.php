<?php
namespace Cyndaron\Minecraft;

use Cyndaron\DBConnection;
use Cyndaron\Page;

class StatusPagina extends Page
{
    public function __construct()
    {
        parent::__construct('Status en landkaart');

        $serverData = DBConnection::doQueryAndFetchAll('SELECT * FROM minecraft_servers ORDER BY name');
        $servers = [];

        foreach ($serverData as $server)
        {
            $serverObj = new Server($server['name'], $server['hostname'], $server['port'], $server['dynmapPort']);
            $serverRet = $serverObj->retrieve();
            $serverRet->id = $server['id'];
            $servers[] = $serverRet;
        }

        $this->render([
            'servers' => $servers,
        ]);
    }
}