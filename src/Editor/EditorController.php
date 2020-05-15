<?php
declare (strict_types = 1);

namespace Cyndaron\Editor;

use Cyndaron\Controller;
use Cyndaron\Module\Linkable;
use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class EditorController extends Controller
{
    protected int $minLevelGet = UserLevel::ADMIN;

    protected static array $editorPages = [];
    protected static array $savePages = [];
    protected static array $internalLinkTypes = [];

    protected function routeGet(): Response
    {
        $type = $this->queryBits->get(1);
        if (!array_key_exists($type, static::$editorPages))
        {
            throw new \Exception('Onbekend paginatype!');
        }

        $class = static::$editorPages[$type];
        $id = $this->queryBits->getInt(2);
        $previous = $this->queryBits->get(3) === 'previous';
        /** @var Page $editorPage */
        $editorPage = new $class($this->getInternalLinks(), $id, $previous);
        $hash = $this->queryBits->get(3);
        $hash = strlen($hash) > 20 ? $hash : '';
        $editorPage->addTemplateVar('hash', $hash);
        return new Response($editorPage->render());
    }

    protected function routePost(): Response
    {
        $type = $this->queryBits->get(1);
        if (!array_key_exists($type, static::$savePages))
        {
            throw new \Exception('Onbekend paginatype!');
        }

        $id = $this->queryBits->getInt(2);

        $class = static::$savePages[$type];
        /** @var EditorSavePage $editorSavePage */
        $editorSavePage = new $class($id);
        return new RedirectResponse($editorSavePage->getReturnUrl() ?: '/');
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
        usort($internalLinks, static function (array $link1, array $link2) {
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