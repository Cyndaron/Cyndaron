<?php
namespace Cyndaron;


class Loguit
{
    public function __construct()
    {
        session_start();
        session_destroy();

        session_start();
        User::addNotification('U bent afgemeld.');
        header('Location: ./');
    }
}
