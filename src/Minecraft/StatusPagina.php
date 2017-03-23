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
            $serverObj = new Server($server['naam'], $server['ip'], $server['port'], $server['dynmapport']);
            $serverObj->retrieve();
            $servers[] = $serverObj;
        }

//        $creative_server_ip = '185.114.156.169';
//        $creative_server_poort = '25852';
//
//        $survival_server_ip = '5.200.23.161';
//        $survival_server_poort = '26176';
//
//        $creative_serverdata = new Server('Creatieve server', $creative_server_ip, $creative_server_poort, 8888);
//        $creative_server = $creative_serverdata->retrieve();
//
//        $survival_serverdata = new Server('Survivalserver', $survival_server_ip, $survival_server_poort, 8888);
//        $survival_server = $survival_serverdata->retrieve();
//
//        $servers = [$creative_server, $survival_server];

        foreach ($servers as $server)
        {
            printf('<h3>%s: ', $server->name);

            if ($server->is_online == true)
            {
                echo 'online</h3>';
                printf('Aantal spelers online: %d (maximaal %d)<br />', $server->online_players, $server->max_players);
                echo 'Versie: ' . $server->game_version . '<br />';
                echo '<abbr title="Message of the day">MOTD</abbr>: ' . $server->motd . '<br /><br />';
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
                echo '<br /><br />';
                printf('<h3>Landkaart %s (<a href="http://%s:%d">Maximaliseren</a>)</h3>', $server->name, $server->hostname, $server->dynmappoort);
                printf('<iframe src="http://%s:%d/" style="border-radius:7px;" width="800" height="600"></iframe>', $server->hostname, $server->dynmappoort);
            }
        }

        parent::toonPostPagina();
    }
}