<?php
declare(strict_types=1);

namespace Cyndaron\Editor;

use Cyndaron\Routing\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Module\Linkable;
use Cyndaron\Page;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

use function Safe\usort;
use function strlen;
use function array_key_exists;
use function var_export;
use function array_merge;

final class EditorController extends Controller
{
    protected int $minLevelGet = UserLevel::ADMIN;

    protected static array $editorPages = [];
    protected static array $savePages = [];
    protected static array $internalLinkTypes = [];

    protected function routeGet(): Response
    {
        $type = $this->queryBits->getString(1);
        if (!array_key_exists($type, static::$editorPages))
        {
            throw new \Exception('Onbekend paginatype: ' . $type);
        }

        $class = static::$editorPages[$type];
        $id = $this->queryBits->getNullableInt(2);
        $previous = $this->queryBits->getString(3) === 'previous';
        /** @var Page $editorPage */
        $editorPage = new $class($this->getInternalLinks(), $id, $previous);
        $hash = $this->queryBits->getString(3);
        $hash = strlen($hash) > 20 ? $hash : '';
        $editorPage->addTemplateVar('hash', $hash);
        return new Response($editorPage->render());
    }

    protected function routePost(RequestParameters $post): Response
    {
        $type = $this->queryBits->getString(1);
        if (!array_key_exists($type, static::$savePages))
        {
            throw new \Exception('Onbekend paginatype: ' . $type);
        }

        $id = $this->queryBits->getNullableInt(2);

        $class = static::$savePages[$type];
        /** @var EditorSavePage $editorSavePage */
        try
        {
            $editorSavePage = new $class($id, $post);
            return new RedirectResponse($editorSavePage->getReturnUrl() ?: '/');
        }
        catch (\PDOException $e)
        {
            $page = new Page('Fout bij opslaan', $e->getFile() . ':' . $e->getLine() . ' ' . $e->getTraceAsString() . PHP_EOL . $e->getMessage() . ': ' . var_export(DBConnection::errorInfo(), true));
            return new Response($page->render(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function getInternalLinks(): array
    {
        $internalLinks = [];
        foreach (static::$internalLinkTypes as $internalLinkType)
        {
            /** @var Linkable $class */
            $class = new $internalLinkType();
            $internalLinks = array_merge($internalLinks, $class->getList());
        }
        usort($internalLinks, static function(array $link1, array $link2)
        {
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
