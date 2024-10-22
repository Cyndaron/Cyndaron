<?php
declare(strict_types=1);

namespace Cyndaron\Gopher\Response;

use Socket;

interface ResponseInterface
{
    public function encode(): string;

    public function send(Socket $socket): void;
}
