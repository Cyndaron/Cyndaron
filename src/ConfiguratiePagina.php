<?php
namespace Cyndaron;

require_once __DIR__ . '/../check.php';
require_once __DIR__ . '/../functies.cyndaron.php';
require_once __DIR__ . '/../functies.db.php';
require_once __DIR__ . '/../functies.pagina.php';

class ConfiguratiePagina extends Pagina
{
    public function __construct()
    {
        if (!postIsLeeg())
        {
            maakInstelling('websitenaam', geefPostVeilig('websitenaam'));
            maakInstelling('websitelogo', geefPostVeilig('websitelogo'));
            maakInstelling('ondertitel', geefPostVeilig('ondertitel'));
            maakInstelling('favicon', geefPostVeilig('favicon'));
            maakInstelling('achtergrondkleur', geefPostVeilig('achtergrondkleur'));
            maakInstelling('menukleur', geefPostVeilig('menukleur'));
            maakInstelling('menuachtergrond', geefPostOnveilig('menuachtergrond'));
            maakInstelling('artikelkleur', geefPostVeilig('artikelkleur'));
            maakInstelling('standaardcategorie', geefPostVeilig('standaardcategorie'));
            maakInstelling('facebook_share', geefPostVeilig('facebook_share'));
            maakInstelling('extra_bodycode', geefPostOnveilig('extra_bodycode'));
            maakInstelling('menutype', geefPostVeilig('menutype'));
            maakInstelling('menuthema', geefPostVeilig('menuthema'));

            $menu = geefPostVeilig('menu');
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
        parent::__construct('Configuratie');
        $this->maakNietDelen(true);
        $this->toonPrePagina();
        $this->connectie = newPDO();
        $this->voegScriptToe('sys/js/test-kleuren.js')

        ?>
        <form method="post" action="configuratie.php" class="form-horizontal">
            <?php
            $fbselected = (geefInstelling('facebook_share') == 1) ? ' checked="checked"' : '';
            $standaardcategorie = geefInstelling('standaardcategorie');
            $categorieen = $this->connectie->prepare('SELECT id,naam FROM categorieen ORDER BY id ASC');
            $categorieen->execute();
            $menu = $this->connectie->prepare('SELECT link,alias FROM menu ORDER BY volgorde ASC;');
            $menu->execute();
            $menustring = $this->menuNaarString($menu);
            $menutype = geefInstelling('menutype');
            $modernMenu = ($menutype !== 'klassiek') ? 'selected' : '';
            $klassiekMenu = ($menutype === 'klassiek') ? 'selected' : '';
            $menuthema = geefInstelling('menuthema');
            $lichtMenu = ($menuthema !== 'donker') ? 'selected' : '';
            $donkerMenu = ($menuthema === 'donker') ? 'selected' : '';

            echo '<div class="form-group"><label class="col-sm-3 control-label">Naam website:</label> <div class="col-sm-6"><input class="form-control" type="text" name="websitenaam" value="' . geefInstelling('websitenaam', TRUE) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Websitelogo:</label> <div class="col-sm-6"><input class="form-control" type="text" name="websitelogo" value="' . geefInstelling('websitelogo', TRUE) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Ondertitel:</label> <div class="col-sm-6"><input class="form-control" type="text" name="ondertitel" value="' . geefInstelling('ondertitel', TRUE) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Websitepictogram:</label> <div class="col-sm-6"><input class="form-control" type="text" name="favicon" value="' . geefInstelling('favicon', TRUE) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Achtergrondkleur hele pagina:</label> <div class="col-sm-6"><input class="form-control" type="text" name="achtergrondkleur" value="' . geefInstelling('achtergrondkleur', TRUE) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Achtergrondkleur menu:</label> <div class="col-sm-6"><input class="form-control" type="text" name="menukleur" value="' . geefInstelling('menukleur', TRUE) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Achtergrondafbeelding menu:</label> <div class="col-sm-6"><input class="form-control" type="text" name="menuachtergrond" value="' . geefInstelling('menuachtergrond', TRUE) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Achtergrondkleur artikel:</label> <div class="col-sm-6"><input class="form-control" type="text" name="artikelkleur" value="' . geefInstelling('artikelkleur', TRUE) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Facebookintegratie:</label><div class="col-sm-6"><input type="checkbox" name="facebook_share" value="1" ' . $fbselected . ' /> Geactiveerd</div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Standaardcategorie:</label> <div class="col-sm-6"><select name="standaardcategorie">';
            echo '<option value="0"';
            if ($standaardcategorie == 0)
            {
                echo ' selected="selected"';
            }
            echo '>Geen</option>';

            foreach ($categorieen as $categorie)
            {
                $selected = '';
                if ($categorie['id'] == $standaardcategorie)
                    $selected = ' selected="selected"';
                echo '<option value="' . $categorie['id'] . '"' . $selected . '>' . $categorie['naam'] . '</option>';
            }
            echo '</select></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Menu</label> <div class="col-sm-6">';
            echo '<input class="form-control" type="text" name="menu" value="' . $menustring . '"/></div></div>';

            printf('<div class="form-group"><label class="col-sm-3 control-label">Menutype:</label><div class="col-sm-6"><select id="menutype" name="menutype"><option value="modern" %s>Modern</option><option value="klassiek" %s>Klassiek</option></select></div></div>', $modernMenu, $klassiekMenu);
            printf('<div class="form-group"><label class="col-sm-3 control-label">Menuthema:</label><div class="col-sm-6"><select id="menuthema" name="menuthema"><option value="licht" %s>Licht</option><option value="donker" %s>Donker</option></select></div></div>', $lichtMenu, $donkerMenu);


            echo '<div class="form-group"><label class="col-sm-3 control-label">Extra bodycode (o.a. Google Analytics)</label> <div class="col-sm-6"><textarea style="height: 75px;" name="extra_bodycode" class="form-control">' . geefInstelling('extra_bodycode') . '</textarea></div></div>';
            ?>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-6">
                    <input class="btn btn-primary" type="submit" value="Opslaan" />
                    <input class="btn btn-default" type="button" id="testKleuren" value="Kleuren testen" />
                </div>
            </div>
        </form>

        <?php
        echo '<h2>Informatie</h2>';
        echo geefProductNaam() . ' ' . geefProductVersie() . ' (' . geefProductCodenaam() . ')<br />';
        echo '© Michael Steenbeek, 2009-2014<br />';
        echo 'Beschikbaar onder de ISC-licentie (zie het bestand LICENSE), m.u.v. van de volgende onderdelen:<ul>';
        echo '<li>Bootstrap: MIT-licentie (LICENSE.Bootstrap)</li>';
        echo '<li>CKeditor: LGPL-licentie (ckeditor/LICENSE.md)</li>';
        echo '<li>jQuery: MIT-licentie (LICENSE.jQuery)</li>';
        echo '<li>Lightbox: MIT-licentie (LICENSE.Lightbox)</li>';
        echo '<li>MinecraftSkinRenderer: BSD-3-licentie (LICENSE.MinecraftSkinRenderer)</li>';
        echo '</ul>';
        echo 'Engineversie: ' . geefCyndaronVersie();
        $this->toonPostPagina();
    }

    protected function menuNaarString($menu): string
    {
        $return = '';
        foreach ($menu as $menuitem)
        {
            $link = htmlentities($menuitem['link'], null, 'UTF-8');
            $alias = htmlentities($menuitem['alias'], null, 'UTF-8');

            $return .= $link . '|' . $alias . ';';
        }
        return $return;
    }
}