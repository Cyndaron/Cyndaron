<?php
namespace Cyndaron\Minecraft;

require_once __DIR__ . '/../../pagina.php';
require_once __DIR__ .'/Server.php';

class StatusPagina extends \Pagina
{
    public function __construct()
    {
        parent::__construct('Status en landkaart');
        parent::toonPrePagina();

        $creative_server_ip = '185.114.156.169';
        $creative_server_poort = '25852';

        $survival_server_ip = '5.200.23.161';
        $survival_server_poort = '26176';

        $creative_serverdata = new Server('Creatieve server', $creative_server_ip, $creative_server_poort);
        $creative_server = $creative_serverdata->retrieve();
        $creative_server->dynmappoort = 8888;

        $survival_serverdata = new Server('Survivalserver', $survival_server_ip, $survival_server_poort);
        $survival_server = $survival_serverdata->retrieve();
        $survival_server->dynmappoort = 8888;

        $servers = array($creative_server, $survival_server);

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
                echo '<br /><br />
		<h3>Landkaart ' . $server->name . ' (<a href="http://' . $server->hostname . ':' . $server->dynmappoort . '">Maximaliseren</a>)</h3>
		<iframe src="http://' . $server->hostname . ':' . $server->dynmappoort . '/" style="border-radius:7px;" width="800" height="600"></iframe>';
            }
        }

        parent::toonPostPagina();
    }
}