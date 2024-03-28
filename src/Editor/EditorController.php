<?php
declare(strict_types=1);

namespace Cyndaron\Editor;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\Error\ErrorPageResponse;
use Cyndaron\Module\Linkable;
use Cyndaron\Page\Page;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\Controller;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\Link;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function array_key_exists;
use function array_merge;
use function strlen;
use function usort;

final class EditorController extends Controller
{
    public array $getRoutes = [
        '' => ['level' => UserLevel::LOGGED_IN, 'function' => 'routeGet'],
    ];
    public array $postRoutes = [
        '' => ['level' => UserLevel::LOGGED_IN, 'function' => 'routePost'],
    ];

    /** @var array<class-string> */
    protected static array $internalLinkTypes = [];

    protected function routeGet(QueryBits $queryBits, User $currentUser, ModuleRegistry $registry): Response
    {
        $type = $queryBits->getString(1);
        if (!array_key_exists($type, $registry->editorPages))
        {
            throw new \Exception('Onbekend paginatype: ' . $type);
        }
        if (!$currentUser->hasRight("{$type}_edit"))
        {
            return new ErrorPageResponse('Fout', 'U heeft onvoldoende rechten om deze functie te gebruiken!', Response::HTTP_UNAUTHORIZED);
        }

        $class = $registry->editorPages[$type];
        $id = $queryBits->getNullableInt(2);
        $previous = $queryBits->getString(3) === 'previous';
        /** @var Page $editorPage */
        $editorPage = new $class($this->getInternalLinks(), $id, $previous);
        $hash = $queryBits->getString(3);
        $hash = strlen($hash) > 20 ? $hash : '';
        $editorPage->addTemplateVar('hash', $hash);
        return new Response($editorPage->render());
    }

    protected function routePost(QueryBits $queryBits, RequestParameters $post, Request $request, User $currentUser, ModuleRegistry $registry): Response
    {
        $type = $queryBits->getString(1);
        if (!array_key_exists($type, $registry->editorSavePages))
        {
            throw new \Exception('Onbekend paginatype: ' . $type);
        }
        if (!$currentUser->hasRight("{$type}_edit"))
        {
            return new ErrorPageResponse('Fout', 'U heeft onvoldoende rechten om deze functie te gebruiken!', Response::HTTP_UNAUTHORIZED);
        }

        $id = $queryBits->getNullableInt(2);

        $class = $registry->editorSavePages[$type];
        try
        {
            /** @var EditorSavePage $editorSavePage */
            $editorSavePage = new $class($id, $post, $request);
            return new RedirectResponse($editorSavePage->getReturnUrl() ?: '/');
        }
        catch (\PDOException $e)
        {
            $page = new SimplePage('Fout bij opslaan', $e->getFile() . ':' . $e->getLine() . ' ' . $e->getTraceAsString() . PHP_EOL . $e->getMessage());
            return new Response($page->render(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @return Link[]
     */
    protected function getInternalLinks(): array
    {
        /** @var Link[] $internalLinks */
        $internalLinks = [];
        foreach (self::$internalLinkTypes as $internalLinkType)
        {
            /** @var Linkable $class */
            $class = new $internalLinkType();
            $internalLinks = array_merge($internalLinks, $class->getList());
        }
        usort($internalLinks, static function(Link $link1, Link $link2)
        {
            return $link1->name <=> $link2->name;
        });
        return $internalLinks;
    }

    /**
     * @param class-string $moduleClass
     * @return void
     */
    public static function addInternalLinkType(string $moduleClass): void
    {
        self::$internalLinkTypes[] = $moduleClass;
    }
}
