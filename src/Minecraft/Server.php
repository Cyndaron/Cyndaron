<?php
/**
 * Copyright (c) 2012 Matt Harzewski
 * Copyright (c) 2015-2017 Michael Steenbeek
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace Cyndaron\Minecraft;

use stdClass;

class Server
{
    protected string $name;
    protected string $hostname;
    protected int $port;
    protected int $dynmapPort;

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
    protected function setHostname(string $hostname): void
    {
        // Overload for hostname:port syntax.
        if (preg_match('/:\d+$/', $hostname))
        {

            // if protocol (e.g., 'http') was included; strip it out
            if (preg_match('/:\/\//', $hostname))
            {
                /** @noinspection PhpUnusedLocalVariableInspection */
                [$protocol, $this->hostname, $this->port] = explode(':', str_replace('//', '', $hostname));
            }
            else
            {
                [$this->hostname, $this->port] = explode(':', $hostname);
            }
        }
        else
        {
            $this->hostname = $hostname;
        }
    }

    public function retrieve(): stdClass
    {
        $socket = @stream_socket_client(sprintf('tcp://%s:%u', $this->hostname, $this->port), $errno, $errstr, 1);

        $stats = new stdClass;
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
        if ($data === false && substr($data, 0, 1) !== "\xFF")
        {
            return $stats;
        }

        $data = substr($data, 9);
        $data = mb_convert_encoding($data, 'UTF-8', 'UCS-2');
        $data = explode("\x00", $data);

        $stats->is_online = true;
        [$stats->protocol_version, $stats->game_version, $stats->motd, $stats->online_players, $stats->max_players] = $data;
        $stats->motd = Util::mineToWeb($stats->motd);

        return $stats;
    }
}