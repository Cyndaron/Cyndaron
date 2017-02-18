<?php
require_once('functies.db.php');
require_once('functies.pagina.php');
require_once('pagina.php');

class FotoalbumPagina extends Pagina
{
    public function __construct()
    {
        $boekid = $_GET['id'];
        if (!is_numeric($boekid) || $boekid < 1)
        {
            header("Location: 404.php");
            die('Incorrecte parameter ontvangen.');
        }
        $boeknaam = geefEen('SELECT naam FROM fotoboeken WHERE id=?', array($boekid));
        $notities = geefEen('SELECT notities FROM fotoboeken WHERE id=?', array($boekid));
        $_SESSION['referrer'] = !empty($_SERVER['HTTP_REFERER']) ? htmlentities($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8') : '';

        $controls = knopcode('bewerken', 'editor.php?type=fotoboek&amp;id=' . $boekid, 'Dit fotoboek bewerken');
        parent::__construct($boeknaam);
        $this->maakTitelknoppen($controls);
        $this->voegScriptToe('/sys/js/lightbox.min.js');
        $this->toonPrepagina();

        if ($dirArray = scandir("./fotoalbums/$boekid"))
        {
            $waregrootte = true;
            $aantal = 0;

            $uitvoer = '<div class="fotoalbum">';

            for ($index = 0; $index < count($dirArray); $index++)
            {
                if (substr($dirArray[$index], 0, 1) != ".")
                {
                    $aantal++;
                    $size = getimagesize('fotoalbums/' . $boekid . '/' . $dirArray[$index]);
                    $width = $size[0];
                    if ($width >= '270')
                    {
                        $waregrootte = false;

                        $fotoLink = 'fotoalbums/' . $boekid . '/' . $dirArray[$index];
                        $thumbnailLink = 'fotoalbums/' . $boekid . 'thumbnails/' . $dirArray[$index];
                        $hash = md5_file($fotoLink);
                        $dataTitleTag = '';
                        if ($bijschrift = geefEen('SELECT bijschrift FROM bijschriften WHERE hash=?', array($hash)))
                        {
                            $dataTitleTag = 'data-title="' . $bijschrift . '"';
                        }

                        $uitvoer .= sprintf('<div class="fotobadge"><a href="%s" data-lightbox="%s" %s data-hash="%s"><img class="thumb" src="fotoalbums/%d', $fotoLink, htmlspecialchars($boeknaam), $dataTitleTag, $hash, $boekid);

                        if (file_exists($thumbnailLink))
                        {
                            $uitvoer .= 'thumbnails/' . $dirArray[$index] . '"';
                        }
                        else
                        {
                            $uitvoer .= '/' . $dirArray[$index] . '" style="width:270px; height:200px"';
                        }
                        $uitvoer .= " alt=\"" . $dirArray[$index] . "\" /></a>";
                        if (isAdmin())
                        {
                            $uitvoer .= '<br>' . knopcode('bewerken', 'editor.php?type=foto&amp;id=' . $hash, 'Bijschrift bewerken', 'Bijschrift bewerken', 16);
                        }
                        $uitvoer .= '</div>';
                    }
                    else
                    {
                        $uitvoer .= "<img class=\"thumb\" src=\"fotoalbums/$boekid/$dirArray[$index]\">";
                    }
                }
            }
            $uitvoer .= '</div>';

            toonIndienAanwezig($notities, '', '');
            if ($aantal == 1)
                echo "Dit album bevat 1 foto.";
            else
                echo "Dit album bevat $aantal foto's.";

            if (!$waregrootte && $aantal == 1)
                echo " Klik op de verkleinde foto om een vergroting te zien.";
            if (!$waregrootte && $aantal != 1)
                echo " Klik op de verkleinde foto's om een vergroting te zien.";

            echo '<br /><br />';
            echo $uitvoer;
        }
        else
        {
            echo 'Dit album bestaat niet.<br />';
        }
        #echo '<br /><a href="' . $_SESSION['referrer'] . "\">Terug</a>\n";
        $this->toonPostPagina();
    }
}

$pagina = new FotoalbumPagina();