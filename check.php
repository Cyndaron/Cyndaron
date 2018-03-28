<?php
/*
* Require deze pagina bovenaan iedere pagina die alleen voor members toegankelijk mag zijn.
* Dit moet helemaal bovenaan, omdat anders de session_start() & header() functie niet werken.
*/
if (empty($_SESSION))
{
    session_start();
}

if (isset($_SESSION['niveau']) && $_SESSION['niveau'] < 4)
{
    new \Cyndaron\Error403Pagina();
    die();
}
else if (!isset($_SESSION['naam']) || $_SESSION['ip'] != $_SERVER['REMOTE_ADDR'])
{
    session_destroy();
    session_start();
    \Cyndaron\Gebruiker::nieuweMelding('U moet inloggen om deze pagina te bekijken');
    $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    die();
}
