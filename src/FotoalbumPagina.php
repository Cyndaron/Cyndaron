<?php
namespace Cyndaron;

use Cyndaron\Widget\Knop;


class FotoalbumPagina extends Pagina
{
    public function __construct()
    {
        $boekid = Request::geefGetVeilig('id');
        if (!is_numeric($boekid) || $boekid < 1)
        {
            header("Location: 404.php");
            die('Incorrecte parameter ontvangen.');
        }
        $boeknaam = DBConnection::geefEen('SELECT naam FROM fotoboeken WHERE id=?', [$boekid]);
        $notities = DBConnection::geefEen('SELECT notities FROM fotoboeken WHERE id=?', [$boekid]);
        $_SESSION['referrer'] = !empty($_SERVER['HTTP_REFERER']) ? htmlentities($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8') : '';

        $controls = new Knop('bewerken', 'editor-fotoalbum?id=' . $boekid, 'Dit fotoboek bewerken');
        parent::__construct($boeknaam);
        $this->maakTitelknoppen($controls);
        $this->voegScriptToe('sys/js/lightbox.min.js');
        $this->toonPrepagina();

        if ($dirArray = @scandir("./fotoalbums/$boekid"))
        {
            $aantal = 0;

            $uitvoer = '<div class="fotoalbum">';

            for ($index = 0; $index < count($dirArray); $index++)
            {
                if (substr($dirArray[$index], 0, 1) != ".")
                {
                    $aantal++;

                    $fotoLink = 'fotoalbums/' . $boekid . '/' . $dirArray[$index];
                    $thumbnailLink = 'fotoalbums/' . $boekid . 'thumbnails/' . $dirArray[$index];
                    $hash = md5_file($fotoLink);
                    $dataTitleTag = '';
                    if ($bijschrift = DBConnection::geefEen('SELECT bijschrift FROM bijschriften WHERE hash=?', [$hash]))
                    {
                        // Vervangen van aanhalingstekens is nodig omdat er links in de beschrijving kunnen zitten.
                        $dataTitleTag = 'data-title="' . str_replace('"', '&quot;', $bijschrift) . '"';
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
                    if (User::isAdmin())
                    {
                        $uitvoer .= '<br>' . new Knop('bewerken', 'editor-foto?id=' . $hash, 'Bijschrift bewerken', 'Bijschrift bewerken', 16);
                    }
                    $uitvoer .= '</div>';

                }
            }
            $uitvoer .= '</div>';

            static::toonIndienAanwezig($notities, '', '');
            if ($aantal == 1)
            {
                echo "Dit album bevat 1 foto.";
            }
            else
            {
                echo "Dit album bevat $aantal foto's.";
            }

            if ($aantal == 1)
            {
                echo " Klik op de verkleinde foto om een vergroting te zien.";
            }
            else
            {
                echo " Klik op de verkleinde foto's om een vergroting te zien.";
            }

            echo '<br /><br />';
            echo $uitvoer;
        }
        else
        {
            echo 'Dit album is leeg.<br />';
        }
        $this->toonPostPagina();
    }
}