<?php
require_once('pagina.php');

$pagina=new Pagina('Bestandenkast');
$pagina->toonPrePagina();
$includefile = './bestandenkast/include.html';
if ($handle = @fopen($includefile, 'r'))
{
	$contents = fread($handle, filesize($includefile));
	preg_match("/<body(.*?)>(.*?)<\\/body>/si", $contents, $match);
	echo $match[2]; 
	/*$pos1=stripos($contents, '<body>')+6;
	$pos2=stripos($contents, '</body>');
	if ($pos1<=6)
		$pos1=0;
	if ($pos2==true)
		$contents=substr($contents, $pos1, $pos2-$pos1);
	else
		$contents=substr($contents, $pos1);
	echo $contents;*/
	fclose($handle);
}

if($bestandendir = @opendir("./bestandenkast"))
{
	// in de juiste vorm gieten
	while($entryName = readdir($bestandendir))
	{
		$dirArray[] = $entryName;
	}
	// en de map sluiten
	closedir($bestandendir);
	// aantal bestanden tellen
	$indexCount = count($dirArray);
	// sorteren
	sort($dirArray);

	// Einde begeleidende tekst, begin bestandenlijst
	echo '<hr />';
	echo '<ul>';
	// nu schaatsen we door de bestanden en schrijven ze weg
	for($index=0; $index < $indexCount; $index++)
	{	
        	if ((substr("$dirArray[$index]", 0, 1) != ".") && (substr("$dirArray[$index]", -4) != "html") && (substr("$dirArray[$index]", -3) != "php")) // verberg eventuele verborgen bestanden plus html- en php-bestanden
		{
			echo '<li><a href="./bestandenkast/'.$dirArray[$index].'">'.pathinfo($dirArray[$index], PATHINFO_FILENAME).'</a></li>';
		}
	}
	echo '</ul>';
}
$pagina->toonPostPagina();

?>
