<?php
namespace Cyndaron\Bestandenkast;

use Cyndaron\Pagina;

class OverzichtPagina extends Pagina
{
    public function __construct()
    {
        parent::__construct('Oefenbestanden');
        $this->showPrePage();
        $includefile = './bestandenkast/include.html';
        if ($handle = @fopen($includefile, 'r'))
        {
            $contents = fread($handle, filesize($includefile));
            preg_match("/<body(.*?)>(.*?)<\\/body>/si", $contents, $match);
            echo $match[2];
            fclose($handle);
        }

        if($bestandendir = @opendir("./bestandenkast"))
        {
            $dirArray = [];

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
                    echo '<li><a href="/bestandenkast/'.$dirArray[$index].'">'.pathinfo($dirArray[$index], PATHINFO_FILENAME).'</a></li>';
                }
            }
            echo '</ul>';
        }
        $this->showPostPage();
    }
}