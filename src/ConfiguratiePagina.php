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
        $this->voegScriptToe('/sys/js/test-kleuren.js')

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

            $formItems = [
                ['name' => 'websitenaam', 'description' => 'Naam website', 'type' => 'text', 'value' => Setting::get('websitenaam', true)],
                ['name' => 'websitelogo', 'description' => 'Websitelogo', 'type' => 'text', 'value' => Setting::get('websitelogo', true)],
                ['name' => 'ondertitel', 'description' => 'Ondertitel', 'type' => 'text', 'value' => Setting::get('ondertitel', true)],
                ['name' => 'favicon', 'description' => 'Websitepictogram', 'type' => 'text', 'value' => Setting::get('favicon', true)],
                ['name' => 'achtergrondkleur', 'description' => 'Achtergrondkleur hele pagina', 'type' => 'color', 'value' => Setting::get('achtergrondkleur', true)],
                ['name' => 'menukleur', 'description' => 'Achtergrondkleur menu', 'type' => 'color', 'value' => Setting::get('menukleur', true)],
                ['name' => 'artikelkleur', 'description' => 'Achtergrondkleur artikel', 'type' => 'color', 'value' => Setting::get('artikelkleur', true)],
                ['name' => 'menuachtergrond', 'description' => 'Achtergrondafbeelding menu', 'type' => 'text', 'value' => Setting::get('menuachtergrond', true)],
                //['name' => 'facebook_share', 'description' => 'Facebookintegratie', 'type' => 'checkbox', 'value' => '1', 'extraAttr' => $fbselected],
            ];

            foreach ($formItems as $formItem): ?>
                <div class="form-group row">
                    <label for="<?=$formItem['name']?>" class="col-md-3 col-form-label col-form-label-md"><?=$formItem['description']?>:</label>
                    <div class="col-md-6">
                        <input type="<?=$formItem['type']?>" class="form-control form-control-md" id="<?=$formItem['name']?>" name="<?=$formItem['name']?>" value="<?=$formItem['value']?>" <?=$formItem['extraAttr'] ?? ''?>/>
                    </div>
                </div>
            <?php endforeach;

            echo '<div class="form-group row"><label class="col-md-3 col-form-label col-form-label-md">Facebookintegratie:</label><div class="col-md-6"><input type="checkbox" id="fbi" name="facebook_share" class="" value="1" ' . $fbselected . ' /> <label for="fbi" class="">Geactiveerd</label></div></div>';
            echo '<div class="form-group row"><label class="col-md-3 col-form-label col-form-label-md">Standaardcategorie:</label><div class="col-md-6"><select name="standaardcategorie" class="custom-select">';
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

            printf('<div class="form-group row"><label class="col-md-3 col-form-label col-form-label-md">Menuthema:</label><div class="col-md-6"><select id="menuthema" name="menuthema" class="custom-select"><option value="licht" %s>Licht</option><option value="donker" %s>Donker</option></select></div></div>', $lichtMenu, $donkerMenu);
            ?>
            <div class="form-group row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <input class="btn btn-primary" type="submit" value="Opslaan"/>
                    <input class="btn btn-outline-cyndaron" type="button" id="testKleuren" value="Kleuren testen"/>
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