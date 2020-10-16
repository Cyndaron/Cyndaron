<?php
declare(strict_types=1);

namespace Cyndaron\Minecraft\Dynmap;

use Cyndaron\Minecraft\Server;
use Cyndaron\Request\QueryBits;
use Cyndaron\Routing\Router;

use function Safe\file_get_contents;
use function Safe\sprintf;

final class DynmapProxy
{
    private string $contents;
    private string $contentType;

    public const MIMETABLE = [
        'css' => 'text/css',
        'ico' => 'image/vnd.microsoft.icon',
        'json' => 'application/json',
        'js' => 'application/javascript',
        'png' => 'image/png',
    ];

    public function __construct(Server $server, QueryBits $queryBits)
    {
        $link = '';
        for ($i = 3; $linkpart = $queryBits->get($i); $i++)
        {
            $link .= '/' . $linkpart;
        }

        $this->contents = $this->getFileContents($link, $server);
        $this->contentType = $this->determineContentType($link);
    }

    /**
     * @param string $link
     * @return string
     */
    public function determineContentType(string $link): string
    {
        foreach (self::MIMETABLE as $extension => $mimetype)
        {
            if (strpos($link, ".$extension") !== false)
            {
                return $mimetype;
            }
        }

        return 'text/html';
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @param string $link
     * @param Server $server
     * @throws \Safe\Exceptions\FilesystemException
     * @throws \Safe\Exceptions\StringsException
     * @return string
     */
    private function getFileContents(string $link, Server $server): string
    {
        $serverAddr = sprintf('http://%s:%d', $server->hostname, $server->dynmapPort);

        if ($link === '' || $link === '/')
        {
            $contents = str_replace('%nonce%', Router::getScriptNonce(), file_get_contents(__DIR__ . '/index.html'));
        }
        else
        {
            $contents = file_get_contents($serverAddr . $link);
        }

        $contents = str_replace(['%SERVER%', $serverAddr], '/minecraft/dynmapproxy/1/', $contents);
        return $contents;
    }
}
