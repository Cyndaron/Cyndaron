<?php
require('check.php');
require_once('functies.pagina.php');
require_once('functies.gebruikers.php');
require_once('pagina.php');

$actie=$_GET['actie'];

if ($actie=='bewerken')
{
	$hash=$_GET['id'];
	$bijschrift=$_POST['artikel'];

	maakBijschrift($hash, $bijschrift);
	nieuweMelding('Bijschrift bewerkt.');
}
