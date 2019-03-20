<?php
namespace Cyndaron\System;

use Cyndaron\CyndaronInfo;
use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\Setting;
use Cyndaron\User\User;
use Cyndaron\Widget\PageTabs;

class SystemPage extends Page
{
    public function __construct($currentPage)
    {

        parent::__construct('Systeembeheer');
        $this->showPrePage();

        echo new PageTabs([
            'config' => 'Configuratie',
            'phpinfo' => 'PHP-info',
            'about' => 'Over ' . CyndaronInfo::PRODUCT_NAME,
        ], '/system/', $currentPage);

        echo '<div class="container-fluid tab-contents">';

        switch ($currentPage)
        {
            case 'about':
                $this->showAboutProduct();
                break;
            case 'phpinfo':
                $this->showPHPInfo();
                break;
            case 'config':
            default:
                $this->showConfigPage();
        }

        echo '<div>';

        $this->showPostPage();
    }

    public function showConfigPage()
    {
        $this->addScript('/src/System/SystemPage.js');

        ?>
        <form method="post" action="/system/config" class="form-horizontal">
            <?php
            $standaardcategorie = Setting::get('standaardcategorie');
            $categorieen = DBConnection::doQueryAndFetchAll('SELECT id,naam FROM categorieen ORDER BY id ASC');
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
                ['name' => 'frontPage', 'description' => 'Voorpagina', 'type' => 'text', 'value' => Setting::get('frontPage', true)],
            ];

            foreach ($formItems as $formItem): ?>
                <div class="form-group row">
                    <label for="<?=$formItem['name']?>" class="col-md-3 col-form-label col-form-label-md"><?=$formItem['description']?>:</label>
                    <div class="col-md-6">
                        <input type="<?=$formItem['type']?>" class="form-control form-control-md" id="<?=$formItem['name']?>" name="<?=$formItem['name']?>" value="<?=$formItem['value']?>" <?=$formItem['extraAttr'] ?? ''?>/>
                    </div>
                </div>
            <?php endforeach;

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
                    <input type="hidden" name="csrfToken" value="<?=User::getCSRFToken('system', 'config')?>"/>
                    <input class="btn btn-primary" type="submit" id="cm-save" value="Opslaan"/>
                    <input class="btn btn-outline-cyndaron" type="button" id="testColors" value="Kleuren testen"/>
                </div>
            </div>
        </form>

        <?php
    }

    public function showPHPInfo()
    {
        // Prevent phpinfo() from writing directly to the screen (we want to change the output first)
        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();

        // We only want the innerhtml of the body.
        preg_match("/<body(.*?)>(.*?)<\\/body>/si", $phpinfo, $match);
        $phpinfo = $match[2];
        // Remove centering
        $phpinfo = str_replace('<div class="center"', '<div', $phpinfo);
        // Enhance table layout
        $phpinfo = str_replace('<table>', '<table class="table">', $phpinfo);
        // Strip links (and with it, logos)
        $phpinfo = preg_replace('/<a href(.*?)>(.*?)<\/a>/', '', $phpinfo);
        // Old, dirty tags that contain inline style attributes as well (which we don't want).
        $phpinfo = preg_replace('/<font(.*?)>/', '', $phpinfo);
        $phpinfo = preg_replace('/<\/font(.*?)>/', '', $phpinfo);

        echo $phpinfo;
    }

    public function showAboutProduct()
    {
        echo CyndaronInfo::PRODUCT_NAME . ' ' . CyndaronInfo::PRODUCT_VERSION . ' (' . CyndaronInfo::PRODUCT_CODENAME . ')<br />';
        echo 'Engineversie: ' . CyndaronInfo::ENGINE_VERSION . '<br />';
        echo '© Michael Steenbeek, 2009-2019<br />';
        echo 'Beschikbaar onder de ISC-licentie (zie het bestand LICENSE), m.u.v. van de volgende onderdelen:<ul>';
        echo '<li>Bootstrap: MIT-licentie (LICENSE.Bootstrap)</li>';
        echo '<li>CKeditor: MPL-, LGPL- en GPL-licenties (contrib/ckeditor/LICENSE.md)</li>';
        echo '<li>jQuery: MIT-licentie (LICENSE.jQuery)</li>';
        echo '<li>Lightbox: MIT-licentie (LICENSE.Lightbox)</li>';
        echo '<li>MCServerStats: MIT-licentie (LICENSE.MCServerStats)</li>';
        echo '<li>MinecraftSkinRenderer: BSD-3-licentie (LICENSE.MinecraftSkinRenderer)</li>';
        echo '</ul>';
    }
}