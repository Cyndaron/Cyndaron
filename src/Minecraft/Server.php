<?php
/**
 * Copyright © 2012 Matt Harzewski
 * Copyright © 2015-2020 Michael Steenbeek
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace Cyndaron\Minecraft;

use Cyndaron\Model;

class Server extends Model
{
    public const TABLE = 'minecraft_servers';
    public const TABLE_FIELDS = ['name', 'hostname', 'port', 'dynmapPort'];

    public string $name = '';
    public string $hostname = '127.0.0.1';
    public int $port = 25565;
    public int $dynmapPort = 8888;

    public bool $isOnline = false;
    public string $protocolVersion;
    public string $gameVersion;
    public string $motd;
    public int $onlinePlayers;
    public int $maxPlayers;

    public function retrieveInfo(): bool
    {
        $socket = @stream_socket_client(sprintf('tcp://%s:%u', $this->hostname, $this->port), $errno, $errstr, 1);

        if (!$socket)
        {
            return false;
        }

        fwrite($socket, "\xfe\x01");
        $data = fread($socket, 1024);
        fclose($socket);

        // Is this a disconnect with the ping?
        if ($data === false || substr($data, 0, 1) !== "\xFF")
        {
            return false;
        }

        $data = substr($data, 9);
        $data = mb_convert_encoding($data, 'UTF-8', 'UCS-2');
        $data = explode("\x00", $data);

        $this->isOnline = true;
        [$this->protocolVersion, $this->gameVersion, $this->motd, $this->onlinePlayers, $this->maxPlayers] = $data;
        $this->motd = Util::mineToWeb($this->motd);

        return true;
    }
}