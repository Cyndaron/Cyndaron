<?php
namespace Cyndaron\System;

use Cyndaron\Category\Category;
use Cyndaron\CyndaronInfo;
use Cyndaron\Page\Page;
use Cyndaron\Translation\Translator;
use Cyndaron\Util\BuiltinSetting;
use Cyndaron\Util\Setting;
use function is_writable;
use function ob_get_clean;
use function Safe\ini_get;
use function Safe\ob_start;
use function Safe\phpinfo;
use function Safe\preg_match;
use function Safe\preg_replace;
use function ksort;
use function strtr;
use function ucfirst;

final class SystemPage extends Page
{
    private const WRITABLE_FILES_AND_FOLDERS = [
        '/cache',
        '/public_html/uploads',
        '/public_html/uploads/images',
    ];

    private const UNWRITEABLE_FILES_AND_FOLDERS = [
        '/public_html/asset',
        '/public_html/contrib',
        '/public_html/css',
        '/public_html/icons',
        '/public_html/js',
        '/public_html/index.php',
        '/public_html/uploads/.htaccess',
        '/public_html/user.css',
        '/sql',
        '/src',
        '/vendor',
        '/config.php',
        '/extra-body-start.php',
        '/extra-body-end.php',
        '/extra-head.php',
    ];

    private const SETTINGS = [
        'post_max_size' => '25M',
        'upload_max_filesize' => '25M',
        'max_file_uploads' => '50',
        'session.cookie_httponly' => '1',
        'session.cookie_secure' => '1',
        'session.use_only_cookies' => '1',
        'session.use_strict_mode' => '1',
        'session.cookie_samesite' => 'Lax',
        'session.use_trans_sid' => '0',
        'memory_limit' => '96M of meer',
    ];
    public function __construct(string $currentPage, Translator $t)
    {
        $this->template = 'System/' . ucfirst($currentPage);
        parent::__construct($t->get('Systeembeheer'));

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
        $this->addScript('/src/System/js/SystemPage.js');

        $this->templateVars['defaultCategory'] = Setting::get('defaultCategory');

        $frontPageIsJumbo = (bool)(int)Setting::get('frontPageIsJumbo');

        $formItems = [
            ['name' => 'siteName', 'description' => 'Naam website', 'type' => 'text', 'value' => Setting::get('siteName')],
            ['name' => 'organisation', 'description' => 'Organisatie', 'type' => 'text', 'value' => Setting::get(BuiltinSetting::ORGANISATION)],
            ['name' => 'shortCode', 'description' => 'Code (3 letters)', 'type' => 'text', 'value' => Setting::get(BuiltinSetting::SHORT_CODE)],
            ['name' => 'logo', 'description' => 'Websitelogo', 'type' => 'text', 'value' => Setting::get('logo')],
            ['name' => 'subTitle', 'description' => 'Ondertitel', 'type' => 'text', 'value' => Setting::get('subTitle')],
            ['name' => 'favicon', 'description' => 'Websitepictogram', 'type' => 'text', 'value' => Setting::get('favicon')],
            ['name' => 'backgroundColor', 'description' => 'Achtergrondkleur hele pagina', 'type' => 'color', 'value' => Setting::get('backgroundColor')],
            ['name' => 'menuColor', 'description' => 'Achtergrondkleur menu', 'type' => 'color', 'value' => Setting::get('menuColor')],
            ['name' => 'articleColor', 'description' => 'Achtergrondkleur artikel', 'type' => 'color', 'value' => Setting::get('articleColor')],
            ['name' => 'accentColor', 'description' => 'Accentkleur', 'type' => 'color', 'value' => Setting::get('accentColor')],
            ['name' => 'menuBackground', 'description' => 'Achtergrondafbeelding menu', 'type' => 'text', 'value' => Setting::get('menuBackground')],
            ['name' => 'frontPage', 'description' => 'Voorpagina', 'type' => 'text', 'value' => Setting::get('frontPage')],
            ['name' => 'frontPageIsJumbo', 'description' => 'Jumbotron op voorpagina', 'type' => 'checkbox', 'value' => 1, 'extraAttr' => $frontPageIsJumbo ? 'checked' : ''],

        ];
        $this->templateVars['formItems'] = $formItems;

        $this->templateVars['categories'] = Category::fetchAllAndSortByName();

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
        if ($phpinfo === false)
        {
            throw new \Exception('Error retrieving PHPinfo!');
        }

        // We only want the innerhtml of the body.
        preg_match("/<body(.*?)>(.*?)<\\/body>/si", $phpinfo, $match);
        $phpInfoText = $match[2] ?? '';
        $phpinfo = strtr($phpInfoText, [
            // Remove centering
            '<div class="center"' => '<div',
            // Enhance table layout
            '<table>' => '<table class="table">',
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
        $settings = $this->getSettings();

        $this->templateVars += [
            'folderResults' => $folderResults,
            'settings' => $settings,
        ];
    }

    /**
     * Checks if folders that need write rights have them,
     * and that folders that shouldn't be writable don't.
     *
     * @return array<string, ExpectedResult>
     */
    private function checkFolderRights(): array
    {
        $folderResults = [];
        foreach (self::WRITABLE_FILES_AND_FOLDERS as $folder)
        {
            $folderResults[$folder] = new ExpectedResult('Schrijfbaar', is_writable(__DIR__ . '/../..' . $folder));
        }
        foreach (self::UNWRITEABLE_FILES_AND_FOLDERS as $folder)
        {
            $folderResults[$folder] = new ExpectedResult('Niet schrijfbaar', !is_writable(__DIR__ . '/../..' . $folder));
        }
        ksort($folderResults);
        return $folderResults;
    }

    /**
     * @throws \Safe\Exceptions\InfoException
     * @return array<string, ExpectedResult>
     */
    private function getSettings(): array
    {
        $ret = [];
        foreach (self::SETTINGS as $setting => $expectedValue)
        {
            $ret[$setting] = new ExpectedResult($expectedValue, ini_get($setting));
        }
        return $ret;
    }
}
