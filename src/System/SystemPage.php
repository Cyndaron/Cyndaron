<?php
namespace Cyndaron\System;

use Cyndaron\Category\Category;
use Cyndaron\CyndaronInfo;
use Cyndaron\Page;
use Cyndaron\Setting;

class SystemPage extends Page
{
    public function __construct(string $currentPage)
    {
        $this->template = 'System/' . ucfirst($currentPage);
        parent::__construct('Systeembeheer');

        $this->templateVars['currentPage'] = $currentPage;
        $this->templateVars['pageTabs'] = [
            'config' => 'Configuratie',
            'phpinfo' => 'PHP-info',
            'checks' => 'Checks',
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
            case 'checks':
                $this->showChecks();
                break;
            case 'config':
            default:
                $this->showConfigPage();
        }
    }

    public function showConfigPage(): void
    {
        $this->addScript('/src/System/SystemPage.js');

        $this->templateVars['defaultCategory'] = Setting::get('defaultCategory');

        $formItems = [
            ['name' => 'siteName', 'description' => 'Naam website', 'type' => 'text', 'value' => Setting::get('siteName')],
            ['name' => 'organisation', 'description' => 'Organisatie', 'type' => 'text', 'value' => Setting::get('organisation')],
            ['name' => 'logo', 'description' => 'Websitelogo', 'type' => 'text', 'value' => Setting::get('logo')],
            ['name' => 'subTitle', 'description' => 'Ondertitel', 'type' => 'text', 'value' => Setting::get('subTitle')],
            ['name' => 'favicon', 'description' => 'Websitepictogram', 'type' => 'text', 'value' => Setting::get('favicon')],
            ['name' => 'backgroundColor', 'description' => 'Achtergrondkleur hele pagina', 'type' => 'color', 'value' => Setting::get('backgroundColor')],
            ['name' => 'menuColor', 'description' => 'Achtergrondkleur menu', 'type' => 'color', 'value' => Setting::get('menuColor')],
            ['name' => 'articleColor', 'description' => 'Achtergrondkleur artikel', 'type' => 'color', 'value' => Setting::get('articleColor')],
            ['name' => 'accentColor', 'description' => 'Accentkleur', 'type' => 'color', 'value' => Setting::get('accentColor')],
            ['name' => 'menuBackground', 'description' => 'Achtergrondafbeelding menu', 'type' => 'text', 'value' => Setting::get('menuBackground')],
            ['name' => 'frontPage', 'description' => 'Voorpagina', 'type' => 'text', 'value' => Setting::get('frontPage')],
            ['name' => 'frontPageIsJumbo', 'description' => 'Jumbotron op voorpagina', 'type' => 'checkbox', 'value' => 1, 'extraAttr' => Setting::get('frontPageIsJumbo') ? 'checked' : ''],

        ];
        $this->templateVars['formItems'] = $formItems;

        $this->templateVars['categories'] = Category::fetchAll([], [], 'ORDER BY name');

        $menuTheme = Setting::get('menuTheme');
        $this->templateVars['lightMenu'] = ($menuTheme !== 'dark') ? 'selected' : '';
        $this->templateVars['darkMenu'] = ($menuTheme === 'dark') ? 'selected' : '';
    }

    public function showPHPInfo(): void
    {
        // Prevent phpinfo() from writing directly to the screen (we want to change the output first)
        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();

        // We only want the innerhtml of the body.
        preg_match("/<body(.*?)>(.*?)<\\/body>/si", $phpinfo, $match);
        $phpinfo = strtr($match[2], [
            // Remove centering
            ['<div class="center"' => '<div'],
            // Enhance table layout
            ['<table>' => '<table class="table">'],
        ]);
        // Strip links (and with it, logos)
        $phpinfo = preg_replace('/<a href(.*?)>(.*?)<\/a>/', '', $phpinfo);
        // Old, dirty tags that contain inline style attributes as well (which we don't want).
        $phpinfo = preg_replace('/<font(.*?)>/', '', $phpinfo);
        $phpinfo = preg_replace('/<\/font(.*?)>/', '', $phpinfo);

        $this->templateVars['phpinfo'] = $phpinfo;
    }

    public function showAboutProduct(): void
    {
        $this->templateVars += [
            'productName' => CyndaronInfo::PRODUCT_NAME,
            'productVersion' => CyndaronInfo::PRODUCT_VERSION,
            'productCodename' => CyndaronInfo::PRODUCT_CODENAME,
            'engineVersion' => CyndaronInfo::ENGINE_VERSION,
        ];
    }

    public function showChecks(): void
    {
        $folderResults = $this->checkFolderRights();

        $this->templateVars += [
            'folderResults' => $folderResults,
        ];
    }

    /**
     * Checks if folders that need write rights have them,
     * and that folders that shouldn't be writable don't.
     *
     * @return array
     */
    private function checkFolderRights(): array
    {
        $writableFolders = [
            '/cache',
            '/uploads',
        ];
        $unWriteableFolders = [
            '/contrib',
            '/sql',
            '/src',
            '/sys',
            '/vendor',
        ];
        $folderResults = [];
        foreach ($writableFolders as $folder)
        {
            $folderResults[$folder] = is_writable(__DIR__ . '/../..' . $folder);
        }
        foreach ($unWriteableFolders as $folder)
        {
            $folderResults[$folder] = !is_writable(__DIR__ . '/../..' . $folder);
        }

        ksort($folderResults);
        return $folderResults;
    }
}
