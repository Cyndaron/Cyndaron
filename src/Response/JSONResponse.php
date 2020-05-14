<?php
namespace Cyndaron\Response;

use Symfony\Component\HttpFoundation\Response;

class JSONResponse extends Response
{
    public function __construct(array $content = [], int $status = 200, array $headers = [])
    {
        $content = json_encode($content, JSON_THROW_ON_ERROR);
        $headers['content-type'] = 'application/json';

        parent::__construct($content, $status, $headers);
    }
}