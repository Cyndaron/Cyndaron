<?php
declare (strict_types = 1);

namespace Cyndaron;

use Cyndaron\User\User;

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
