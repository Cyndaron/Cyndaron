<?php
require_once('check.php');
require_once('functies.pagina.php');
require_once('functies.db.php');
require_once('functies.cyndaron.php');
require_once('pagina.php');
if ($_POST)
{
    maakInstelling('websitenaam', $_POST['websitenaam']);
    maakInstelling('ondertitel', $_POST['ondertitel']);
    maakInstelling('paneel', $_POST['paneel']);
    maakInstelling('favicon', $_POST['favicon']);
    maakInstelling('achtergrondkleur', $_POST['achtergrondkleur']);
    maakInstelling('menukleur', $_POST['menukleur']);
    maakInstelling('menuachtergrond', $_POST['menuachtergrond']);
    maakInstelling('artikelkleur', $_POST['artikelkleur']);
    maakInstelling('standaardcategorie', $_POST['standaardcategorie']);
    maakInstelling('facebook_share', @$_POST['facebook_share']);
    maakInstelling('extra_bodycode', $_POST['extra_bodycode']);
    $menu = $_POST['menu'];
    $split1 = explode(';', $menu);
    $nieuwmenu = null;

    foreach ($split1 as $split2)
    {
        $menuitem = explode('|', $split2);

        if ($menuitem[0])
        {
            $nieuwmenu[] = array('link' => $menuitem[0], 'alias' => $menuitem[1]);
        }
    }
    vervangMenu($nieuwmenu);
}
$pagina = new Pagina('Configuratie');
$pagina->maakNietDelen(true);
$pagina->toonPrePagina();
?>
    <script type="text/javascript" src="/sys/js/test-kleuren.js"></script>
    <form method="post" action="configuratie.php" class="form-horizontal">
        <table>
<?php
$fbselected = (geefInstelling('facebook_share') == 1) ? ' checked="checked"' : '';

echo '<div class="form-group"><label class="col-sm-3 control-label">Naam website:</label> <div class="col-sm-6"><input class="form-control" type="text" name="websitenaam" value="' . geefInstelling('websitenaam', TRUE) . '" /></div></div>';
echo '<div class="form-group"><label class="col-sm-3 control-label">Ondertitel:</label> <div class="col-sm-6"><input class="form-control" type="text" name="ondertitel" value="' . geefInstelling('ondertitel', TRUE) . '" /></div></div>';
echo '<div class="form-group"><label class="col-sm-3 control-label">Paneel:</label> <div class="col-sm-6"><input class="form-control" type="text" name="paneel" value="' . geefInstelling('paneel', TRUE) . '" /></div></div>';
echo '<div class="form-group"><label class="col-sm-3 control-label">Websitepictogram:</label> <div class="col-sm-6"><input class="form-control" type="text" name="favicon" value="' . geefInstelling('favicon', TRUE) . '" /></div></div>';
echo '<div class="form-group"><label class="col-sm-3 control-label">Achtergrondkleur hele pagina:</label> <div class="col-sm-6"><input class="form-control" type="text" name="achtergrondkleur" value="' . geefInstelling('achtergrondkleur', TRUE) . '" /></div></div>';
echo '<div class="form-group"><label class="col-sm-3 control-label">Achtergrondkleur menu:</label> <div class="col-sm-6"><input class="form-control" type="text" name="menukleur" value="' . geefInstelling('menukleur', TRUE) . '" /></div></div>';
echo '<div class="form-group"><label class="col-sm-3 control-label">Achtergrondafbeelding menu:</label> <div class="col-sm-6"><input class="form-control" type="text" name="menuachtergrond" value="' . geefInstelling('menuachtergrond', TRUE) . '" /></div></div>';
echo '<div class="form-group"><label class="col-sm-3 control-label">Achtergrondkleur artikel:</label> <div class="col-sm-6"><input class="form-control" type="text" name="artikelkleur" value="' . geefInstelling('artikelkleur', TRUE) . '" /></div></div>';
echo '<div class="form-group"><label class="col-sm-3 control-label">Facebookintegratie:</label><div class="col-sm-6"><input type="checkbox" name="facebook_share" value="1"' . $fbselected . ' /> Geactiveerd</div></div>';
echo '<div class="form-group"><label class="col-sm-3 control-label">Standaardcategorie:</label> <div class="col-sm-6"><select name="standaardcategorie">';
echo '<option value="0"';
$standaardcategorie = geefInstelling('standaardcategorie');
if ($standaardcategorie == 0)
{
    echo ' selected="selected"';
}
echo '>Geen</option>';
$connectie = newPDO();
$categorieen = $connectie->prepare('SELECT id,naam FROM categorieen ORDER BY id ASC');
$categorieen->execute();
foreach ($categorieen as $categorie)
{
    if ($categorie['id'] == $standaardcategorie)
        $selected = ' selected="selected"';
    echo '<option value="' . $categorie['id'] . '"' . $selected . '>' . $categorie['naam'] . '</option>';
}
echo '</select></div></div>';
echo '<div class="form-group"><label class="col-sm-3 control-label">Menu</label> <div class="col-sm-6"><input class="form-control" type="text" name="menu" value="';

$menu = $connectie->prepare('SELECT link,alias FROM menu ORDER BY volgorde ASC;');
$menu->execute();
foreach ($menu as $menuitem)
{
    $link = htmlentities($menuitem['link'], null, 'UTF-8');
    $alias = htmlentities($menuitem['alias'], null, 'UTF-8');

    echo $link . '|' . $alias . ';';
}
echo '"/></div></div>';
echo '<div class="form-group"><label class="col-sm-3 control-label">Extra bodycode (o.a. Google Analytics)</label> <div class="col-sm-6"><textarea style="width: 225px; height: 75px;" name="extra_bodycode">' . geefInstelling('extra_bodycode') . '</textarea></div></div>';
?>
<div class="form-group">
    <div class="col-sm-offset-3 col-sm-6">
        <input class="btn btn-primary" type="submit" value="Opslaan" />
        <input class="btn btn-default" type="button" onclick="testkleuren();" value="Kleuren testen" />
    </div>
</div>
</form>

<?php
echo '<h2>Informatie</h2>';
echo geefProductNaam() . ' ' . geefProductVersie() . ' (' . geefProductCodenaam() . ')<br />';
echo '© Michael Steenbeek, 2009-2014<br />';
echo 'Beschikbaar onder de ISC-licentie (zie het bestand LICENSE), m.u.v. CKeditor.<br />';
echo 'CKeditor gebruikt onder LGPL-licentie.<br />';
echo 'Engineversie: ' . geefCyndaronVersie();
$pagina->toonPostPagina();
