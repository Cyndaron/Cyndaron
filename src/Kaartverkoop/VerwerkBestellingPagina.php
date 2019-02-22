<?php
namespace Cyndaron\Kaartverkoop;

use Cyndaron\Pagina;

class VerwerkBestellingPagina extends Pagina
{
    public function __construct(string $title, string $message)
    {
        parent::__construct($title);
        $this->toonPrePagina();
        echo $message;
        $this->toonPostPagina();
    }
}