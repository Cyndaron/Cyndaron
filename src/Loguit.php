<?php
namespace Cyndaron;


class Loguit
{
    public function __construct()
    {
        session_start();
        session_destroy();

        session_start();
        Gebruiker::nieuweMelding('U bent afgemeld.');
        header('Location: ./');
    }
}
