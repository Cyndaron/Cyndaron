<?php
namespace Cyndaron\System;

use Cyndaron\CyndaronInfo;
use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\Setting;

class SystemPage extends Page
{
    public function __construct($currentPage)
    {
        $this->template = $currentPage . '.twig';
        parent::__construct('Systeembeheer');

        $this->twigVars['currentPage'] = $currentPage;
        $this->twigVars['pageTabs'] = [
            'config' => 'Configuratie',
            'phpinfo' => 'PHP-info',
            'about' => 'Over ' . CyndaronInfo::PRODUCT_NAME,
        ];

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

        $this->render();
    }

    public function showConfigPage()
    {
        $this->addScript('/src/System/SystemPage.js');

        $this->twigVars['defaultCategory'] = Setting::get('defaultCategory');

        $formItems = [
            ['name' => 'siteName', 'description' => 'Naam website', 'type' => 'text', 'value' => Setting::get('siteName', true)],
            ['name' => 'logo', 'description' => 'Websitelogo', 'type' => 'text', 'value' => Setting::get('logo', true)],
            ['name' => 'subTitle', 'description' => 'Ondertitel', 'type' => 'text', 'value' => Setting::get('subTitle', true)],
            ['name' => 'favicon', 'description' => 'Websitepictogram', 'type' => 'text', 'value' => Setting::get('favicon', true)],
            ['name' => 'backgroundColor', 'description' => 'Achtergrondkleur hele pagina', 'type' => 'color', 'value' => Setting::get('backgroundColor', true)],
            ['name' => 'menuColor', 'description' => 'Achtergrondkleur menu', 'type' => 'color', 'value' => Setting::get('menuColor', true)],
            ['name' => 'articleColor', 'description' => 'Achtergrondkleur artikel', 'type' => 'color', 'value' => Setting::get('articleColor', true)],
            ['name' => 'accentColor', 'description' => 'Accentkleur', 'type' => 'color', 'value' => Setting::get('accentColor', true)],
            ['name' => 'menuBackground', 'description' => 'Achtergrondafbeelding menu', 'type' => 'text', 'value' => Setting::get('menuBackground', true)],
            ['name' => 'frontPage', 'description' => 'Voorpagina', 'type' => 'text', 'value' => Setting::get('frontPage', true)],
        ];
        $this->twigVars['formItems'] = $formItems;

        $categories = DBConnection::doQueryAndFetchAll('SELECT id,name FROM categories ORDER BY id ASC');
        $this->twigVars['categories'] = $categories;

        $menuTheme = Setting::get('menuTheme');
        $this->twigVars['lightMenu'] = ($menuTheme !== 'dark') ? 'selected' : '';
        $this->twigVars['darkMenu'] = ($menuTheme === 'dark') ? 'selected' : '';
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

        $this->twigVars['phpinfo'] = $phpinfo;
    }

    public function showAboutProduct()
    {
        $this->twigVars += [
            'productName' => CyndaronInfo::PRODUCT_NAME,
            'productVersion' => CyndaronInfo::PRODUCT_VERSION,
            'productCodename' => CyndaronInfo::PRODUCT_CODENAME,
            'engineVersion' => CyndaronInfo::ENGINE_VERSION,
        ];
    }
}