<?php
require_once('functies.php');
require_once('pagina.php');
$pagina=new Pagina('Spelers');
$pagina->toonPrePagina();

$connectie=newPDO();
$spelers=$connectie->query("SELECT * FROM leden ORDER BY niveau DESC, mcnaam ASC");
$niveau[0]="In de Goelag";
$niveau[1]="Aspirant-lid";
$niveau[2]="Lid";
$niveau[3]="Moderator";
$niveau[4]="Medebeheerder";
$niveau[5]="Eeuwige Dictator en Geliefde Leider van TXcraft";

$laatsteniveau=0;

foreach ($spelers as $speler)
{
	if ($speler['niveau']>=3 && $laatsteniveau==0)
		echo '<h2 style="border-bottom: 1px dotted;">Politbureau</h2>';
	if ($speler['niveau']==2 && $laatsteniveau>=3)
		echo '<h2 style="border-bottom: 1px dotted;">Leden</h2>';
	if ($speler['niveau']==1 && $laatsteniveau>=2)
		echo '<h2 style="border-bottom: 1px dotted;">Aspirant-leden</h2>';
	if ($speler['niveau']==0 && $laatsteniveau>=1)
		echo '<h2 style="border-bottom: 1px dotted;">In de Goelag</h2>';
	$laatsteniveau=$speler['niveau'];
	$vooraanzicht = "3d/cf-mcskin.php?vr=-10&hr=20&hrh=0&vrla=0&vrra=0&vrll=0&vrrl=0&ratio=4&format=png&displayHair=false&headOnly=false&user={$speler['mcnaam']}";
	$achteraanzicht = "3d/cf-mcskin.php?vr=-10&hr=200&hrh=0&vrla=0&vrra=0&vrll=0&vrrl=0&ratio=4&format=png&displayHair=false&headOnly=false&user={$speler['mcnaam']}";
	//3d.php?a=-10&w=20&wt=0&abg=0&abd=0&ajg=0&ajd=0&ratio=20&format=png&displayHairs=true&headOnly=false&login='.$speler['mcnaam'].'

	echo '<div style="display: inline-block; overflow:hidden;">';
	echo '<table>';
	echo '<tr><td style="width: 100px; padding: 10px 30px 10px 30px;">';
//style="height: 130px;"
	echo '<img alt="Avatar van '.$speler['echtenaam'].'" title="Avatar van '.$speler['echtenaam'].'" src="'.$vooraanzicht.'" onmouseover="this.src=\''.$achteraanzicht.'\'" onmouseout="this.src=\''.$vooraanzicht.'\'" />';
	echo '</td>';
	echo '<td  style="width: 350px; padding: 10px 10px 10px 10px; vertical-align: middle;">';

	echo '<span style="font-family: Trebuchet MS, Arial, sans-serif; font-size: 40px;">'.$speler['mcnaam'].'</span>';

	if ($speler['donateur']==1)
	{
		echo '<br /><span style="font-weight: bold; color: #B8860B;">Donateur</span>';
	}

	echo '<br />Echte naam: '.$speler['echtenaam'];
	echo '<br />Status: '.$speler['status'];

	if ($speler['niveau']>=3 && $speler['niveau']<=5)
	{
		echo '<br />Niveau: ';
		echo $niveau[$speler['niveau']];
	}

	/*if ($speler['whovian']==1)
	{
		echo '<br />Klasse: <abbr title="Deze gebruiker kijkt naar Doctor Who">Timelord</abbr>';
	}
	elseif($speler['whovian']==2)
	{	
		echo '<br />Klasse: <abbr title="Deze gebruiker kijkt niet naar Doctor Who">Weeping Angels</abbr>';
	}*/

	echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';
}

$pagina->toonPostPagina();
?>
