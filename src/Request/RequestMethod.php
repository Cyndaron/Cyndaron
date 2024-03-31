<?php
declare(strict_types=1);

namespace Cyndaron\Request;

enum RequestMethod : string
{
    case GET = 'GET';
    case POST = 'POST';
}
