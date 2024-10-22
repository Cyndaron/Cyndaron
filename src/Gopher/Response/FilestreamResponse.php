<?php
declare(strict_types=1);

namespace Cyndaron\Gopher\Response;

use Socket;
use SplFileObject;
use function socket_write;

class FilestreamResponse implements ResponseInterface
{
    public function __construct(private readonly string $filename)
    {
    }

    public function encode(): string
    {
        return \Safe\file_get_contents($this->filename);
    }

    public function send(Socket $socket): void
    {
        $handle = new SplFileObject($this->filename);
        while (!$handle->eof() && $data = $handle->fread(1024))
        {
            @socket_write($socket, $data);
        }
    }
}
