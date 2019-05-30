<?php
namespace Cyndaron\Minecraft;

use Cyndaron\DBConnection;
use Cyndaron\Page;

class StatusPagina extends Page
{
    public function __construct()
    {
        parent::__construct('Status en landkaart');
        parent::showPrePage();

        $serverData = DBConnection::doQueryAndFetchAll('SELECT * FROM minecraft_servers ORDER BY name');
        $servers = [];

        foreach ($serverData as $server)
        {
            $serverObj = new Server($server['name'], $server['hostname'], $server['port'], $server['dynmapPort']);
            $serverRet = $serverObj->retrieve();
            $serverRet->id = $server['id'];
            $servers[] = $serverRet;
        }

        foreach ($servers as $server)
        {
            printf('<br /><h3>%s: ', $server->name);

            if ($server->is_online == true)
            {
                echo 'online</h3>';
                printf('Aantal spelers online: %d (maximaal %d)<br />', $server->online_players, $server->max_players);
                echo 'Versie: ' . $server->game_version . '<br />';
                echo '<abbr title="Message of the day">MOTD</abbr>: ' . $server->motd . '<br />';
            }
            else
            {
                echo 'offline</h3>';
            }
        }

        foreach ($servers as $server)
        {
            if ($server->is_online == true)
            {
                echo '<br />';
                printf('<h3>Landkaart %s <a href="http://%s:%d" class="btn btn-outline-cyndaron" role="button"><span class="glyphicon glyphicon-resize-full"></span> Maximaliseren</a></h3><br>', $server->name, $server->hostname, $server->dynmapPort);
                printf('<iframe src="/minecraft/dynmapproxy/%d/" style="border-radius:7px;" width="800" height="600"></iframe>', $server->id);
                echo '<br />';
            }
        }

        parent::showPostPage();
    }
}