<?php
namespace Cyndaron;

require_once __DIR__ . '/../check.php';

class ConfiguratiePagina extends Pagina
{
    public function __construct()
    {
        if (!Request::postIsLeeg())
        {
            Instelling::maakInstelling('websitenaam', Request::geefPostVeilig('websitenaam'));
            Instelling::maakInstelling('websitelogo', Request::geefPostVeilig('websitelogo'));
            Instelling::maakInstelling('ondertitel', Request::geefPostVeilig('ondertitel'));
            Instelling::maakInstelling('favicon', Request::geefPostVeilig('favicon'));
            Instelling::maakInstelling('achtergrondkleur', Request::geefPostVeilig('achtergrondkleur'));
            Instelling::maakInstelling('menukleur', Request::geefPostVeilig('menukleur'));
            Instelling::maakInstelling('menuachtergrond', Request::geefPostOnveilig('menuachtergrond'));
            Instelling::maakInstelling('artikelkleur', Request::geefPostVeilig('artikelkleur'));
            Instelling::maakInstelling('standaardcategorie', Request::geefPostVeilig('standaardcategorie'));
            Instelling::maakInstelling('facebook_share', Request::geefPostVeilig('facebook_share'));
            Instelling::maakInstelling('extra_bodycode', Request::geefPostOnveilig('extra_bodycode'));
            Instelling::maakInstelling('menutype', Request::geefPostVeilig('menutype'));
            Instelling::maakInstelling('menuthema', Request::geefPostVeilig('menuthema'));

            $menu = Request::geefPostVeilig('menu');
            $split1 = explode(';', $menu);
            $nieuwmenu = null;

            foreach ($split1 as $split2)
            {
                $menuitem = explode('|', $split2);

                if ($menuitem[0])
                {
                    $nieuwmenu[] = ['link' => $menuitem[0], 'alias' => $menuitem[1]];
                }
            }
            MenuModel::vervangMenu($nieuwmenu);
        }
        parent::__construct('Configuratie');
        $this->maakNietDelen(true);
        $this->toonPrePagina();
        $this->connectie = DBConnection::getPDO();
        $this->voegScriptToe('sys/js/test-kleuren.js')

        ?>
        <form method="post" action="configuratie" class="form-horizontal">
            <?php
            $fbselected = (Instelling::geefInstelling('facebook_share') == 1) ? ' checked="checked"' : '';
            $standaardcategorie = Instelling::geefInstelling('standaardcategorie');
            $categorieen = $this->connectie->prepare('SELECT id,naam FROM categorieen ORDER BY id ASC');
            $categorieen->execute();
            $menu = $this->connectie->prepare('SELECT link,alias FROM menu ORDER BY volgorde ASC;');
            $menu->execute();
            $menustring = $this->menuNaarString($menu);
            $menutype = Instelling::geefInstelling('menutype');
            $modernMenu = ($menutype !== 'klassiek') ? 'selected' : '';
            $klassiekMenu = ($menutype === 'klassiek') ? 'selected' : '';
            $menuthema = Instelling::geefInstelling('menuthema');
            $lichtMenu = ($menuthema !== 'donker') ? 'selected' : '';
            $donkerMenu = ($menuthema === 'donker') ? 'selected' : '';

            echo '<div class="form-group"><label class="col-sm-3 control-label">Naam website:</label> <div class="col-sm-6"><input class="form-control" type="text" name="websitenaam" value="' . Instelling::geefInstelling('websitenaam', true) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Websitelogo:</label> <div class="col-sm-6"><input class="form-control" type="text" name="websitelogo" value="' . Instelling::geefInstelling('websitelogo', true) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Ondertitel:</label> <div class="col-sm-6"><input class="form-control" type="text" name="ondertitel" value="' . Instelling::geefInstelling('ondertitel', true) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Websitepictogram:</label> <div class="col-sm-6"><input class="form-control" type="text" name="favicon" value="' . Instelling::geefInstelling('favicon', true) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Achtergrondkleur hele pagina:</label> <div class="col-sm-6"><input class="form-control" type="text" name="achtergrondkleur" value="' . Instelling::geefInstelling('achtergrondkleur', true) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Achtergrondkleur menu:</label> <div class="col-sm-6"><input class="form-control" type="text" name="menukleur" value="' . Instelling::geefInstelling('menukleur', true) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Achtergrondafbeelding menu:</label> <div class="col-sm-6"><input class="form-control" type="text" name="menuachtergrond" value="' . Instelling::geefInstelling('menuachtergrond', true) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Achtergrondkleur artikel:</label> <div class="col-sm-6"><input class="form-control" type="text" name="artikelkleur" value="' . Instelling::geefInstelling('artikelkleur', true) . '" /></div></div>';
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
                {
                    $selected = ' selected="selected"';
                }
                echo '<option value="' . $categorie['id'] . '"' . $selected . '>' . $categorie['naam'] . '</option>';
            }
            echo '</select></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Menu</label> <div class="col-sm-6">';
            echo '<input class="form-control" type="text" name="menu" value="' . $menustring . '"/></div></div>';

            printf('<div class="form-group"><label class="col-sm-3 control-label">Menutype:</label><div class="col-sm-6"><select id="menutype" name="menutype"><option value="modern" %s>Modern</option><option value="klassiek" %s>Klassiek</option></select></div></div>', $modernMenu, $klassiekMenu);
            printf('<div class="form-group"><label class="col-sm-3 control-label">Menuthema:</label><div class="col-sm-6"><select id="menuthema" name="menuthema"><option value="licht" %s>Licht</option><option value="donker" %s>Donker</option></select></div></div>', $lichtMenu, $donkerMenu);


            echo '<div class="form-group"><label class="col-sm-3 control-label">Extra bodycode (o.a. Google Analytics)</label> <div class="col-sm-6"><textarea style="height: 75px;" name="extra_bodycode" class="form-control">' . Instelling::geefInstelling('extra_bodycode') . '</textarea></div></div>';
            ?>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-6">
                    <input class="btn btn-primary" type="submit" value="Opslaan"/>
                    <input class="btn btn-default" type="button" id="testKleuren" value="Kleuren testen"/>
                </div>
            </div>
        </form>

        <?php
        echo '<h2>Informatie</h2>';
        echo CyndaronInfo::PRODUCT_NAAM . ' ' . CyndaronInfo::PRODUCT_VERSIE . ' (' . CyndaronInfo::PRODUCT_CODENAAM . ')<br />';
        echo '© Michael Steenbeek, 2009-2017<br />';
        echo 'Beschikbaar onder de ISC-licentie (zie het bestand LICENSE), m.u.v. van de volgende onderdelen:<ul>';
        echo '<li>Bootstrap: MIT-licentie (LICENSE.Bootstrap)</li>';
        echo '<li>CKeditor: MPL-, LGPL- en GPL-licenties (ckeditor/LICENSE.md)</li>';
        echo '<li>jQuery: MIT-licentie (LICENSE.jQuery)</li>';
        echo '<li>Lightbox: MIT-licentie (LICENSE.Lightbox)</li>';
        echo '<li>MCServerStats: MIT-licentie (LICENSE.MCServerStats)</li>';
        echo '<li>MinecraftSkinRenderer: BSD-3-licentie (LICENSE.MinecraftSkinRenderer)</li>';
        echo '</ul>';
        echo 'Engineversie: ' . CyndaronInfo::ENGINE_VERSIE;
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