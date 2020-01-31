<?php
declare (strict_types = 1);

namespace Cyndaron\Minecraft\Dynmap;

use Cyndaron\DBConnection;
use Cyndaron\Request;

class DynmapProxy
{
    public const MIMETABLE = [
        'css' => 'text/css',
        'ico' => 'image/vnd.microsoft.icon',
        'js' => 'application/javascript',
        'png' => 'image/png',
    ];
    public function __construct()
    {
        $serverId = Request::getVar(2);
        $server = DBConnection::doQueryAndFetchFirstRow('SELECT * FROM minecraft_servers WHERE id = ?', [$serverId]);
        if (!$server)
        {
            die('');
        }
        $serverAddr = sprintf('http://%s:%d', $server['hostname'], (int)$server['dynmapPort']);


        $link = '';

        for ($i = 3; $linkpart = Request::getVar($i); $i++)
        {
            $link .= '/' . $linkpart;
        }

        if ($link === '' || $link === '/')
        {
            $contents = file_get_contents(__DIR__ . '/index.html');
        }
        else
        {
            $contents = file_get_contents($serverAddr . $link);
        }

        foreach (self::MIMETABLE as $extension => $mimetype)
        {
            if (strpos($link, ".$extension") !== false)
            {
                header('Content-Type: ' . $mimetype);
                break;
            }
        }

        $contents = str_replace(['%SERVER%', $serverAddr], '/minecraft/dynmapproxy/1/', $contents);
        echo $contents;
    }
}