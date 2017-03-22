<?php
namespace Cyndaron;

require_once __DIR__ . '/../functies.gebruikers.php';

class Loguit
{
    public function __construct()
    {
        session_start();
        session_destroy();

        session_start();
        nieuweMelding('U bent afgemeld.');
        header('Location: ./');
    }
}
