<?php
declare (strict_types = 1);

namespace Cyndaron\Editor;

use Cyndaron\Controller;
use Cyndaron\Module\Linkable;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;

class EditorController extends Controller
{
    protected int $minLevelGet = UserLevel::ADMIN;

    protected static array $editorPages = [];
    protected static array $savePages = [];
    protected static array $internalLinkTypes = [];

    protected function routeGet()
    {
        $type = Request::getVar(1);
        if (array_key_exists($type, static::$editorPages))
        {
            $class = static::$editorPages[$type];
            new $class($this->getInternalLinks());
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

    protected function getInternalLinks(): array
    {
        $internalLinks = [];
        foreach (static::$internalLinkTypes as $internalLinkType)
        {
            /** @var Linkable $class */
            $class = new $internalLinkType;
            $internalLinks = array_merge($internalLinks, $class->getList());
        }
        usort($internalLinks, function (array $link1, array $link2) {
            return $link1['name'] <=> $link2['name'];
        });
        return $internalLinks;
    }

    public static function addEditorPage(array $page): void
    {
        static::$editorPages = array_merge(static::$editorPages, $page);
    }

    public static function addEditorSavePage(array $page): void
    {
        static::$savePages = array_merge(static::$savePages, $page);
    }

    public static function addInternalLinkType(string $moduleClass): void
    {
        static::$internalLinkTypes[] = $moduleClass;
    }
}