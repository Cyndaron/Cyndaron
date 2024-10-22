<?php
declare(strict_types=1);

namespace Cyndaron\Gopher\Response;

use Socket;
use function socket_write;

trait SendNonStreamTrait
{
    public function send(Socket $socket): void
    {
        socket_write($socket, $this->encode());
    }
}
