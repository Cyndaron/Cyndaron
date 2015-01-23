<?php
$heeftTitel=true;

if ($id)
{
	$content=geefEen('SELECT notities FROM fotoboeken WHERE id=?', array($id));
	$titel=geefEen('SELECT naam FROM fotoboeken WHERE id=?',array($id));
}

// Verplicht.
function toonSpecifiekeKnoppen()
{
}
?>
