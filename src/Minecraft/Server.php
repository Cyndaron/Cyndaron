<?php
namespace Cyndaron\Minecraft;

class Server
{
    protected $name;
    protected $hostname;
    protected $port;
    protected $dynmapPort;

    public function __construct(string $name, string $hostname = '127.0.0.1', int $port = 25565, int $dynmapPort = 8888)
    {
        $this->name = $name;
        $this->port = $port;
        $this->dynmapPort = $dynmapPort;
        $this->setHostname($hostname);
    }

    /**
     * Set the hostname of the server.
     *
     * @param string $hostname The hostname. Must be IP or domain (only IPv4).
     */
    protected function setHostname(string $hostname)
    {
        // Overload for hostname:port syntax.
        if (preg_match('/:\d+$/', $hostname))
        {

            // if protocol (e.g., 'http') was included; strip it out
            if (preg_match('/:\/\//', $hostname))
            {
                /** @noinspection PhpUnusedLocalVariableInspection */
                list($protocol, $this->hostname, $this->port) = explode(':', str_replace('//', '', $hostname));
            }
            else
            {
                list($this->hostname, $this->port) = explode(':', $hostname);
            }
        }
        else
        {
            $this->hostname = $hostname;
        }
    }

    function retrieve(): \stdClass
    {
        $socket = @stream_socket_client(sprintf('tcp://%s:%u', $this->hostname, $this->port), $errno, $errstr, 1);

        $stats = new \stdClass;
        $stats->hostname = $this->hostname;
        $stats->port = $this->port;
        $stats->dynmapPort = $this->dynmapPort;
        $stats->name = $this->name;
        $stats->is_online = false;

        if (!$socket)
        {
            return $stats;
        }

        fwrite($socket, "\xfe\x01");
        $data = fread($socket, 1024);
        fclose($socket);

        // Is this a disconnect with the ping?
        if ($data == false && substr($data, 0, 1) != "\xFF")
        {
            return $stats;
        }

        $data = substr($data, 9);
        $data = mb_convert_encoding($data, 'auto', 'UCS-2');
        $data = explode("\x00", $data);

        $stats->is_online = true;
        list($stats->protocol_version, $stats->game_version, $stats->motd, $stats->online_players, $stats->max_players) = $data;
        $stats->motd = mb_convert_encoding(Util::mineToWeb($stats->motd), 'UTF-8', 'ISO-8859-1');

        return $stats;
    }
}