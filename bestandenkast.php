<?php
require_once('pagina.php');

class Bestandenkast extends Pagina
{
    public function __construct()
    {
        parent::__construct('Bestandenkast');
        $this->toonPrePagina();
        $includefile = './bestandenkast/include.html';
        if ($handle = @fopen($includefile, 'r'))
        {
            $contents = fread($handle, filesize($includefile));
            preg_match("/<body(.*?)>(.*?)<\\/body>/si", $contents, $match);
            echo $match[2];
            fclose($handle);
        }

        if ($bestanden = scandir("./bestandenkast"))
        {
            // Einde begeleidende tekst, begin bestandenlijst
            echo '<hr />';
            echo '<ul>';

            for ($index = 0; $index < count($bestanden); $index++)
            {
                if ((substr("$bestanden[$index]", 0, 1) != ".") && (substr("$bestanden[$index]", -4) != "html") && (substr("$bestanden[$index]", -3) != "php")) // verberg eventuele verborgen bestanden plus html- en php-bestanden
                {
                    echo '<li><a href="./bestandenkast/' . $bestanden[$index] . '">' . pathinfo($bestanden[$index], PATHINFO_FILENAME) . '</a></li>';
                }
            }
            echo '</ul>';
        }
        $this->toonPostPagina();
    }
}

$pagina = new Bestandenkast();