<?php
declare (strict_types = 1);

namespace Cyndaron\Minecraft\Dynmap;

use Cyndaron\DBConnection;
use Cyndaron\Minecraft\Server;
use Cyndaron\Request;

class DynmapProxy
{
    public const MIMETABLE = [
        'css' => 'text/css',
        'ico' => 'image/vnd.microsoft.icon',
        'js' => 'application/javascript',
        'png' => 'image/png',
    ];

    public function __construct(Server $server)
    {
        $link = '';
        for ($i = 3; $linkpart = Request::getVar($i); $i++)
        {
            $link .= '/' . $linkpart;
        }

        $contents = $this->getFileContents($link, $server);

        $this->sendContentTypeHeader($link);

        echo $contents;
    }

    /**
     * @param string $link
     */
    private function sendContentTypeHeader(string $link): void
    {
        foreach (self::MIMETABLE as $extension => $mimetype)
        {
            if (strpos($link, ".$extension") !== false)
            {
                header('Content-Type: ' . $mimetype);
                break;
            }
        }
    }

    /**
     * @param string $link
     * @param Server $server
     * @return false|string
     */
    private function getFileContents(string $link, Server $server)
    {
        $serverAddr = sprintf('http://%s:%d', $server->hostname, $server->dynmapPort);

        if ($link === '' || $link === '/')
        {
            $contents = file_get_contents(__DIR__ . '/index.html');
        }
        else
        {
            $contents = file_get_contents($serverAddr . $link);
        }

        $contents = str_replace(['%SERVER%', $serverAddr], '/minecraft/dynmapproxy/1/', $contents);
        return $contents;
    }
}