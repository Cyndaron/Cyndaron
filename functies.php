<?php
// Beheren van pagina's/artikelen
require_once('functies.pagina.php');
// Meertaligheid
require_once('functies.lingo.php');
// URLs
require_once('functies.url.php');
// Database
require_once('functies.db.php');
// Gebruikers
require_once('functies.gebruikers.php');

if (!$_SESSION)
	session_start();

function versie()
{
	return "4.0 alpha 4";
}
function codenaam()
{
	return "Roma";
}
?>
