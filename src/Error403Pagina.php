<?php
namespace Cyndaron;

class Error403Pagina extends Pagina
{
    public function __construct()
    {
        header('HTTP/1.0 403 Forbidden');

        parent::__construct('403: Forbidden');
        $this->toonPrePagina();
        echo 'U heeft geprobeerd een pagina op te vragen die niet mag worden opgevraagd.';
        $this->toonPostPagina();
    }
}
