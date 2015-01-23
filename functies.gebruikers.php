<?php
function isAdmin()
{
	if (!isset($_SESSION['naam']) OR $_SESSION['ip'] != $_SERVER['REMOTE_ADDR'] OR $_SESSION['niveau']<4)
	{
		return false;
	}
	else
	{
		return true;
	}
}
function toonIndienAanwezigEnAdmin($string, $voor=null, $na=null)
{
	if (isAdmin() && $string)
	{
		echo $voor;
		echo $string;
		echo $na;
	}
}
function toonIndienAanwezigEnGeenAdmin($string, $voor=null, $na=null)
{
	if (!isAdmin() && $string)
	{
		echo $voor;
		echo $string;
		echo $na;
	}
}

function nieuweMelding($tekst)
{
	$_SESSION['meldingen'][]=$tekst;
}
function geefMeldingen()
{
	$return=$_SESSION['meldingen'];
	$_SESSION['meldingen']=null;
	return $return;

}