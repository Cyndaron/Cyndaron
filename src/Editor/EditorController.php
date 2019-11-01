<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */
declare (strict_types = 1);

namespace Cyndaron\Editor;

use Cyndaron\Controller;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;

class EditorController extends Controller
{
    protected $minLevelGet = UserLevel::ADMIN;

    protected static $editorPages = [
        'category' => \Cyndaron\Category\EditorPage::class,
        'mailform' => \Cyndaron\Mailform\EditorPage::class,
        'photo' => \Cyndaron\Photoalbum\EditorPagePhoto::class,
        'photoalbum' => \Cyndaron\Photoalbum\EditorPage::class,
        'sub' => \Cyndaron\StaticPage\EditorPage::class,
    ];
    protected static $savePages = [
        'category' => \Cyndaron\Category\EditorSavePage::class,
        'mailform' => \Cyndaron\Mailform\EditorSavePage::class,
        'photo' => \Cyndaron\Photoalbum\EditorSavePagePhoto::class,
        'photoalbum' => \Cyndaron\Photoalbum\EditorSavePage::class,
        'sub' => \Cyndaron\StaticPage\EditorSavePage::class,
    ];

    protected function routeGet()
    {
        $type = Request::getVar(1);
        if (array_key_exists($type, static::$editorPages))
        {
            $class = static::$editorPages[$type];
            new $class;
        }
    }

    protected function routePost()
    {
        $type = Request::getVar(1);
        if (array_key_exists($type, static::$savePages))
        {
            $class = static::$savePages[$type];
            new $class;
        }
    }

    public static function addEditorPage(array $page): void
    {
        static::$editorPages = array_merge(static::$editorPages, $page);
    }

    public static function addEditorSavePage(array $page): void
    {
        static::$savePages = array_merge(static::$savePages, $page);
    }

}