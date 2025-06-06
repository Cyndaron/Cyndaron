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
namespace Cyndaron\Minecraft\Server;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;
use Cyndaron\Minecraft\MinecraftString;
use ErrorException;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\StreamException;
use function explode;
use function Safe\fclose;
use function Safe\fread;
use function Safe\fwrite;
use function Safe\mb_convert_encoding;
use function Safe\stream_socket_client;
use function sprintf;
use function substr;

final class Server extends Model
{
    public const TABLE = 'minecraft_servers';

    #[DatabaseField]
    public string $name = '';
    #[DatabaseField]
    public string $hostname = '127.0.0.1';
    #[DatabaseField]
    public int $port = 25565;
    #[DatabaseField]
    public int $dynmapPort = 8888;

    public bool $isOnline = false;
    public string $protocolVersion;
    public string $gameVersion;
    public string $motd;
    public int $onlinePlayers;
    public int $maxPlayers;

    public function retrieveInfo(): bool
    {
        try
        {
            $socket = @stream_socket_client(sprintf('tcp://%s:%u', $this->hostname, $this->port), $errno, $errstr, 1);
        }
        catch (StreamException)
        {
            return false;
        }
        catch (ErrorException)
        {
            return false;
        }

        try
        {
            fwrite($socket, "\xfe\x01");
            $data = fread($socket, 1024);
            fclose($socket);
        }
        catch (FilesystemException)
        {
            return false;
        }

        // Is this a disconnect with the ping?
        if (substr($data, 0, 1) !== "\xFF")
        {
            return false;
        }

        $data = substr($data, 9);
        /** @var string $data */
        $data = mb_convert_encoding($data, 'UTF-8', 'UCS-2');
        $data = explode("\x00", $data);

        $this->isOnline = true;
        $this->protocolVersion = $data[0];
        $this->gameVersion = $data[1];
        $this->motd = $data[2];
        $this->onlinePlayers = (int)$data[3];
        $this->maxPlayers = (int)$data[4];
        $motd = new MinecraftString($this->motd);
        $this->motd = $motd->toHtml();

        return true;
    }
}
