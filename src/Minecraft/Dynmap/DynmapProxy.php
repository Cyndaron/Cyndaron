<?php
declare (strict_types = 1);

namespace Cyndaron\Minecraft\Dynmap;

use Cyndaron\DBConnection;
use Cyndaron\Request;
use finfo;

class DynmapProxy
{
    const MIMETABLE = [
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
        $serverAddr = sprintf('http://%s:%d', $server['hostname'], intval($server['dynmapPort']));


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


        // Coerce false to 0.
//        $pos1 = intval(strrpos($link, '?'));
//        $pos = strrpos($link, '.', $pos1);
//        if ($pos !== false)
//        {
//            $extension = substr($link, $pos);
//            var_dump($extension);
//            if (array_key_exists($extension, self::MIMETABLE))
//            {
//                header('Content-Type: ' . self::MIMETABLE[$extension]);
//            }
//        }

        foreach (self::MIMETABLE as $extension => $mimetype)
        {
            if (strpos($link, ".$extension") !== false)
            {
                header('Content-Type: ' . $mimetype);
                break;
            }
        }


//        $ch = curl_init('http://185.114.156.169:8888/' . $link);
//        curl_setopt($ch, CURLOPT_HEADER, 0);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
//        $contents = curl_exec($ch);
//
//
//        $finfo = new finfo(FILEINFO_MIME_TYPE);
//        $mimeType = $finfo->buffer($contents);

        $contents = str_replace('%SERVER%', '/minecraft/dynmapproxy/1/', $contents);
        $contents = str_replace($serverAddr, '/minecraft/dynmapproxy/1/', $contents);
        echo $contents;


    }
}