<?php
namespace Cyndaron;

require_once __DIR__ . '/../check.php';

class ConfiguratiePagina extends Pagina
{
    public function __construct()
    {
        if (!Request::postIsLeeg())
        {
            Setting::set('websitenaam', Request::geefPostVeilig('websitenaam'));
            Setting::set('websitelogo', Request::geefPostVeilig('websitelogo'));
            Setting::set('ondertitel', Request::geefPostVeilig('ondertitel'));
            Setting::set('favicon', Request::geefPostVeilig('favicon'));
            Setting::set('achtergrondkleur', Request::geefPostVeilig('achtergrondkleur'));
            Setting::set('menukleur', Request::geefPostVeilig('menukleur'));
            Setting::set('menuachtergrond', Request::geefPostOnveilig('menuachtergrond'));
            Setting::set('artikelkleur', Request::geefPostVeilig('artikelkleur'));
            Setting::set('standaardcategorie', Request::geefPostVeilig('standaardcategorie'));
            Setting::set('facebook_share', Request::geefPostVeilig('facebook_share'));
            Setting::set('menuthema', Request::geefPostVeilig('menuthema'));
        }
        parent::__construct('Configuratie');
        $this->maakNietDelen(true);
        $this->toonPrePagina();
        $this->connectie = DBConnection::getPDO();
        $this->voegScriptToe('sys/js/test-kleuren.js')

        ?>
        <form method="post" action="configuratie" class="form-horizontal">
            <?php
            $fbselected = (Setting::get('facebook_share') == 1) ? ' checked="checked"' : '';
            $standaardcategorie = Setting::get('standaardcategorie');
            $categorieen = $this->connectie->prepare('SELECT id,naam FROM categorieen ORDER BY id ASC');
            $categorieen->execute();
            $menuthema = Setting::get('menuthema');
            $lichtMenu = ($menuthema !== 'donker') ? 'selected' : '';
            $donkerMenu = ($menuthema === 'donker') ? 'selected' : '';

            echo '<div class="form-group"><label class="col-sm-3 control-label">Naam website:</label> <div class="col-sm-6"><input class="form-control" type="text" name="websitenaam" value="' . Setting::get('websitenaam', true) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Websitelogo:</label> <div class="col-sm-6"><input class="form-control" type="text" name="websitelogo" value="' . Setting::get('websitelogo', true) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Ondertitel:</label> <div class="col-sm-6"><input class="form-control" type="text" name="ondertitel" value="' . Setting::get('ondertitel', true) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Websitepictogram:</label> <div class="col-sm-6"><input class="form-control" type="text" name="favicon" value="' . Setting::get('favicon', true) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Achtergrondkleur hele pagina:</label> <div class="col-sm-6"><input class="form-control" type="text" name="achtergrondkleur" value="' . Setting::get('achtergrondkleur', true) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Achtergrondkleur menu:</label> <div class="col-sm-6"><input class="form-control" type="text" name="menukleur" value="' . Setting::get('menukleur', true) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Achtergrondafbeelding menu:</label> <div class="col-sm-6"><input class="form-control" type="text" name="menuachtergrond" value="' . Setting::get('menuachtergrond', true) . '" /></div></div>';
            echo '<div class="form-group"><label class="col-sm-3 control-label">Achtergrondkleur artikel:</label> <div class="col-sm-6"><input class="form-control" type="text" name="artikelkleur" value="' . Setting::get('artikelkleur', true) . '" /></div></div>';
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

            printf('<div class="form-group"><label class="col-sm-3 control-label">Menuthema:</label><div class="col-sm-6"><select id="menuthema" name="menuthema"><option value="licht" %s>Licht</option><option value="donker" %s>Donker</option></select></div></div>', $lichtMenu, $donkerMenu);
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
        echo 'Engineversie: ' . CyndaronInfo::ENGINE_VERSIE . '<br />';
        echo '© Michael Steenbeek, 2009-2019<br />';
        echo 'Beschikbaar onder de ISC-licentie (zie het bestand LICENSE), m.u.v. van de volgende onderdelen:<ul>';
        echo '<li>Bootstrap: MIT-licentie (LICENSE.Bootstrap)</li>';
        echo '<li>CKeditor: MPL-, LGPL- en GPL-licenties (ckeditor/LICENSE.md)</li>';
        echo '<li>jQuery: MIT-licentie (LICENSE.jQuery)</li>';
        echo '<li>Lightbox: MIT-licentie (LICENSE.Lightbox)</li>';
        echo '<li>MCServerStats: MIT-licentie (LICENSE.MCServerStats)</li>';
        echo '<li>MinecraftSkinRenderer: BSD-3-licentie (LICENSE.MinecraftSkinRenderer)</li>';
        echo '</ul>';
        $this->toonPostPagina();
    }
}