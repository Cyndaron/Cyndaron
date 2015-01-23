<?php
$heeftTitel=false;

if ($id)
{
	$content=geefEen('SELECT bijschrift FROM bijschriften WHERE hash=?', array($id));
}

function toonSpecifiekeKnoppen()
{

}

?>
