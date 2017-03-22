<?php
namespace Cyndaron;

class Error404Pagina extends Pagina
{
    public function __construct()
    {
        header('HTTP/1.0 404 Not Found');

        parent::__construct('404: Not Found');
        $this->maakNietDelen(true);
        $this->toonPrePagina();
        echo 'U heeft geprobeerd een pagina op te vragen die niet kon worden gevonden.';
        $this->toonPostPagina();

    }
}

