<?php
declare(strict_types=1);

namespace Cyndaron\System;

use Cyndaron\Page\Page;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Translation\Translator;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;
use function Safe\ini_get;
use function is_writable;
use function ksort;

final class ChecksPage
{
    use SystemPageTrait;

    private const WRITABLE_FILES_AND_FOLDERS = [
        '/public_html/uploads',
        '/public_html/uploads/images',
        '/var',
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

    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly Translator $t
    ) {
    }

    #[RouteAttribute('checks', RequestMethod::GET, UserLevel::ADMIN)]
    public function show(): Response
    {
        $page = new Page();
        $page->template = 'System/Checks';
        $this->setCommonVariables($page, 'checks');

        $folderResults = $this->checkFolderRights();
        $settings = $this->getSettings();

        $page->templateVars += [
            'folderResults' => $folderResults,
            'settings' => $settings,
        ];

        return $this->pageRenderer->renderResponse($page);
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
