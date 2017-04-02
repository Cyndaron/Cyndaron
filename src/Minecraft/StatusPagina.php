<?php
namespace Cyndaron\Minecraft;

use Cyndaron\DBConnection;
use Cyndaron\Pagina;

class StatusPagina extends Pagina
{
    public function __construct()
    {
        parent::__construct('Status en landkaart');
        parent::toonPrePagina();

        $connectie = DBConnection::getInstance();
        $serverData = $connectie->doQueryAndFetchAll('SELECT * FROM mc_servers ORDER BY naam');
        $servers = [];

        foreach ($serverData as $server)
        {
            $serverObj = new Server($server['naam'], $server['hostname'], $server['port'], $server['dynmapPort']);
            $servers[] = $serverObj->retrieve();
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
                printf('<h3>Landkaart %s <a href="http://%s:%d" class="btn btn-default" role="button"><span class="glyphicon glyphicon-resize-full"></span> Maximaliseren</a></h3><br>', $server->name, $server->hostname, $server->dynmapPort);
                printf('<iframe src="http://%s:%d/" style="border-radius:7px;" width="800" height="600"></iframe>', $server->hostname, $server->dynmapPort);
                echo '<br />';
            }
        }

        parent::toonPostPagina();
    }
}