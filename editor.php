<?php
require_once('check.php');
require_once('functies.db.php');
require_once('functies.url.php');
require_once('pagina.php');

$id = geefGetVeilig('id');
$vorigeversie = geefGetVeilig('vorigeversie');
$vvstring = $vorigeversie ? 'vorige' : '';

$type = geefGetVeilig('type');
$heeftTitel = true;

if (@file_exists('editor.' . $type . '.php'))
{
    require('editor.' . $type . '.php');
}
else
{
    die ('Ongeldig paginatype!');
}

$_SESSION['referrer'] = htmlentities($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8');

// Zorgen voor juiste codering
$content = !empty($content) ? htmlentities($content, ENT_QUOTES, 'UTF-8') : '';
if (empty($titel))
    $titel = '';

$pagina = new Pagina('Editor');
$pagina->maakNietDelen(true);
$pagina->toonPrePagina();
?>
    <script src="ckeditor/ckeditor.js"></script>
    <script type="text/javascript">
        /* <![CDATA[ */
        function plakLink()
        {
            CKEDITOR.tools.callFunction(185, this)
            setTimeout(function ()
            {
                var input = document.getElementById('cke_110_select');
                input.value = "";

                var input2 = document.getElementById('cke_113_textInput');
                var link = document.getElementById("verwijzing");
                input2.value = link.value;
            }, 800);
        }
        /* ]]> */
    </script>
<?php
echo '<form name="bewerkartikel" method="post" action="bewerk.php?id=' . $id . '&amp;type=' . $type . '&amp;actie=bewerken">';

$unfriendlyUrl = 'toon' . $type . '.php?id=' . $id;
$friendlyUrl = geefFriendlyUrl($unfriendlyUrl);
if ($unfriendlyUrl == $friendlyUrl)
{
    $friendlyUrl = "";
}

echo '<table>';
if ($heeftTitel === true)
    echo '<tr><td class="tablesys">Titel: <input type="text" name="titel" value="' . $titel . '" /></td></tr>';
$dir = dirname($_SERVER['PHP_SELF']);
if ($dir == '/')
    $dir = '';
echo '<tr><td class="tablesys">Friendly URL: http://' . $_SERVER['HTTP_HOST'] . $dir . '/<input type="text" name="friendlyUrl" value="' . $friendlyUrl . '" /></td></tr>
<tr><td class="tablesys">
<textarea class="ckeditor" name="artikel" rows="25" cols="125">';
echo $content; ?>
    </textarea>

    </td></tr>
    <tr>
        <td class="tablesys">Interne link maken: <select id="verwijzing"><?php
                $connectie = newPDO();
                $sql = "
SELECT * FROM (SELECT CONCAT('toonsub.php?id=', id) AS link, CONCAT('Sub: ', naam) AS naam FROM subs ORDER BY naam ASC) AS twee
UNION
SELECT * FROM (SELECT CONCAT('tooncategorie.php?id=', id) AS link, CONCAT('Categorie: ', naam) AS naam FROM categorieen ORDER BY naam ASC) AS drie
UNION
SELECT * FROM (SELECT CONCAT('toonfotoboek.php?id=', id) AS link, CONCAT('Fotoboek: ', naam) AS naam FROM fotoboeken ORDER BY naam ASC) AS vijf;";

                $links = $connectie->prepare($sql);
                $links->execute();

                foreach ($links->fetchAll() as $link)
                {
                    echo '<option value="' . $link['link'] . '">' . $link['naam'] . '</option>';
                } ?>
            </select>
            <input type="button" onclick="plakLink()" value="Invoegen"/>
        </td>
    </tr>
    <tr>
        <td class="tablesys"><?php

            toonSpecifiekeKnoppen();

            echo '<input type="submit" value="Opslaan" />';
            echo '<input type="button" value="Annuleren" onclick="location.href=\'';
            echo $_SESSION['referrer'];
            echo '\';" />';

            ?>
        </td>
    </tr>
    </table>
    </form>
<?php
$pagina->toonPostPagina();
