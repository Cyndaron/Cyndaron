<?php
require_once('functies.php');
require_once('pagina.php');
error_reporting(E_ALL & ~E_NOTICE);

$pagina=new Pagina('Status en landkaart');
$pagina->toonPrePagina();

$creative_server_ip='185.114.156.169';
$creative_server_poort='25852';

$survival_server_ip='5.200.23.161';
//$survival_server_ip = 'txcraftsurvival.subdome.in';
$survival_server_poort='26176';

$creative_serverdata = new Server('Creatieve server', $creative_server_ip, $creative_server_poort);
$creative_server = retrieve($creative_serverdata);
$creative_server->dynmappoort=8888;

$survival_serverdata = new Server('Survivalserver', $survival_server_ip, $survival_server_poort);
$survival_server = retrieve($survival_serverdata);
$survival_server->dynmappoort=8888;

$servers = array($creative_server, $survival_server);

foreach($servers as $server)
{
	printf('<h3>%s: ', $server->name);

	if ($server->is_online==true)
	{
		echo 'online</h3>';
		printf('Aantal spelers online: %d (maximaal %d)<br />', $server->online_players, $server->max_players);
		echo 'Versie: '.$server->game_version.'<br />';
		echo '<abbr title="Message of the day">MOTD</abbr>: '.$server->motd.'<br /><br />';
	}
	else
	{
		echo 'offline</h3>';
	}
}

foreach($servers as $server)
{
	if (1 == 1 || $server->is_online==true)
	{
		echo '<br /><br />
		<h3>Landkaart '.$server->name.' (<a href="http://'.$server->hostname.':'.$server->dynmappoort.'">Maximaliseren</a>)</h3>
		<iframe src="http://'.$server->hostname.':'.$server->dynmappoort.'/" style="border-radius:7px;" width="800" height="600"></iframe>';
	}
}

$pagina->toonPostPagina();

class Server {

	protected $name;
	protected $hostname;
	protected $port;

	public function __construct($name, $hostname = '127.0.0.1', $port = 25565) {
		$this->name = $name;
		$this->setPort($port);
		$this->setHostname($hostname);
	}


	/**
	* Must be IP or domain. (only IPv4)
	*/
	public function setHostname($hostname) {

	// Overload for hostname:port syntax.
		if( preg_match('/:\d+$/', $hostname) ) {

			// if protocol (e.g., 'http') was included; strip it out
			if( preg_match('/:\/\//', $hostname) ) {
				list($protocol, $this->hostname, $this->port) = explode(':', str_replace('//', '', $hostname));
			} else {
				list($this->hostname, $this->port) = explode(':', $hostname);
			}
		} else {
			$this->hostname = $hostname;
		}
	}

	public function getHostname() {
		return $this->hostname;
	}

	public function setPort($port) {
		if(is_int($port)) {
			$this->port = $port;
		} else if( is_numeric($port) ) {
			$this->port = intval($port);
		}
	}

	public function getPort() {
		return $this->port;
	}

	public function getName() {
		return $this->name;
	}
}


function retrieve($server) {

	$socket = @stream_socket_client(sprintf('tcp://%s:%u', $server->getHostname(), $server->getPort()), $errno, $errstr, 1);

	$stats = new \stdClass;
	$stats->hostname = $server->getHostname();
	$stats->port = $server->getPort();
	$stats->name = $server->getName();
	$stats->is_online = false;

	if (!$socket)
		return $stats;

	fwrite($socket, "\xfe\x01");
	$data = fread($socket, 1024);
	fclose($socket);

	// Is this a disconnect with the ping?
	if($data == false AND substr($data, 0, 1) != "\xFF")
		return $stats;

	$data = substr($data, 9);
	$data = mb_convert_encoding($data, 'auto', 'UCS-2');
	$data = explode("\x00", $data);

	$stats->is_online = true;
	list($stats->protocol_version, $stats->game_version, $stats->motd, $stats->online_players, $stats->max_players) = $data;
	$stats->motd = mb_convert_encoding(mineToWeb($stats->motd), 'UTF-8', 'ISO-8859-1');

	return $stats;
}

function mineToWeb($minetext){
    preg_match_all("/[^§&]*[^§&]|[§&][0-9a-z][^§&]*/", $minetext, $brokenupstrings); 
    $returnstring = "";
    foreach ($brokenupstrings as $results){
        $ending = '';
        foreach ($results as $individual){
            $code = preg_split("/[&§][0-9a-z]/", $individual);
            preg_match("/[&§][0-9a-z]/", $individual, $prefix);
            if (isset($prefix[0])){
                $actualcode = substr($prefix[0], 1);
                switch ($actualcode){
                case "1":
                    $returnstring = $returnstring.'<span style="color:#0000AA;">';
                    $ending = $ending ."</span>";
                    break;
                case "2":
                    $returnstring = $returnstring.'<span style="color:#00AA00;">';
                    $ending = $ending ."</span>";
                    break;
                case "3":
                    $returnstring = $returnstring.'<span style="color:#00AAAA;">';
                    $ending = $ending ."</span>";
                    break;
                case "4":
                    $returnstring = $returnstring.'<span style="color:#AA0000;">';
                    $ending =$ending ."</span>";
                    break;
                case "5":
                    $returnstring = $returnstring.'<span style="color:#AA00AA;">';
                    $ending = $ending . "</span>";
                    break;
                case "6":
                    $returnstring = $returnstring.'<span style="color:#FFAA00;">';
                    $ending = $ending ."</span>";
                    break;
                case "7":
                    $returnstring = $returnstring.'<span style="color:#AAAAAA;">';
                    $ending = $ending ."</span>";
                    break;
                case "8":
                    $returnstring = $returnstring.'<span style="color:#555555;">';
                    $ending = $ending ."</span>";
                    break;
                case "9":
                    $returnstring = $returnstring.'<span style="color:#5555FF;">';
                    $ending = $ending . "</span>";
                    break;
                case "a":
                    $returnstring = $returnstring.'<span style="color:#55FF55;">';
                    $ending = $ending ."</span>";
                    break;
                case "b":
                    $returnstring = $returnstring.'<span style="color:#55FFFF;">';
                    $ending = $ending ."</span>";
                    break;
                case "c":
                    $returnstring = $returnstring.'<span style="color:#FF5555;">';
                    $ending = $ending ."</span>";
                    break;
                case "d":
                    $returnstring = $returnstring.'<span style="color:#FF55FF;">';
                    $ending = $ending ."</span>";
                    break;
                case "e":
                    $returnstring = $returnstring.'<span style="color:#FFFF55;">';
                    $ending = $ending ."</span>";
                    break;
                case "f":
                    $returnstring = $returnstring.'<span style="color:#FFFFFF;">';
                    $ending = $ending ."</span>";
                    break;
                case "l":
                    if (strlen($individual)>2){
                    $returnstring = $returnstring.'<span style="font-weight:bold;">';
                    $ending =  "</span>".$ending;
                    break;
                    }
                case "m":
                    if (strlen($individual)>2){
                    $returnstring = $returnstring.'<span style=" text-decoration:line-through;">';
                    $ending = "</span>".$ending;
                    break;
                    }
                case "n":
                    if (strlen($individual)>2){
                    $returnstring = $returnstring.'<span style="text-decoration: underline;">';
                    $ending = "</span>".$ending;
                    break;
                    }
                case "o":
                    if (strlen($individual)>2){
                    $returnstring = $returnstring.'<span style="font-style: italic;">';
                    $ending ="</span>".$ending;
                    break;
                    }
                case "r":
                    $returnstring = $returnstring.$ending;
                    $ending = '';
                    break;
                }
                    if (isset($code[1])){
                            $returnstring = $returnstring.$code[1];
                            if (isset($ending)&&strlen($individual)>2){
                                $returnstring = $returnstring.$ending;
                                $ending = '';
                                }
                    }
            }
            else{
                $returnstring = $returnstring.$individual;
            }

       }
}

return $returnstring;
}
