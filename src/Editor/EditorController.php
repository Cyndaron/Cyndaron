<?php
declare(strict_types=1);

namespace Cyndaron\Editor;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\DBAL\Connection;
use Cyndaron\Error\ErrorPage;
use Cyndaron\Imaging\ImageExtractor;
use Cyndaron\Module\Linkable;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\Controller;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Url\UrlService;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\DependencyInjectionContainer;
use Cyndaron\Util\Link;
use Cyndaron\Util\Util;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use function array_key_exists;
use function array_merge;
use function strlen;
use function usort;

final class EditorController extends Controller
{
    private const IMAGE_DIR = Util::UPLOAD_DIR . '/images/via-editor';

    #[RouteAttribute('', RequestMethod::GET, UserLevel::LOGGED_IN)]
    public function routeGet(QueryBits $queryBits, User $currentUser, ModuleRegistry $registry, Connection $connection, UrlService $urlService): Response
    {
        $type = $queryBits->getString(1);
        if (!array_key_exists($type, $registry->editorPages))
        {
            throw new \Exception('Onbekend paginatype: ' . $type);
        }
        if (!$currentUser->hasRight("{$type}_edit"))
        {
            return $this->pageRenderer->renderErrorResponse(new ErrorPage('Fout', 'U heeft onvoldoende rechten om deze functie te gebruiken!', Response::HTTP_UNAUTHORIZED));
        }

        /** @var class-string<EditorPage> $class */
        $class = $registry->editorPages[$type];
        $id = $queryBits->getNullableInt(2);
        $previous = $queryBits->getString(3) === 'previous';
        /** @var EditorPage $editorPage */
        $editorPage = new $class($queryBits, $urlService, $this->getInternalLinks($registry->internalLinkTypes, $connection), $id, $previous);
        $hash = $queryBits->getString(3);
        $hash = strlen($hash) > 20 ? $hash : '';
        $editorPage->addTemplateVar('hash', $hash);
        return $this->pageRenderer->renderResponse($editorPage);
    }

    #[RouteAttribute('', RequestMethod::POST, UserLevel::LOGGED_IN)]
    public function routePost(QueryBits $queryBits, DependencyInjectionContainer $dic, RequestParameters $post, User $currentUser, ModuleRegistry $registry, UrlService $urlService): Response
    {
        $type = $queryBits->getString(1);
        if (!array_key_exists($type, $registry->editorSavePages))
        {
            throw new \Exception('Onbekend paginatype: ' . $type);
        }
        if (!$currentUser->hasRight("{$type}_edit"))
        {
            return $this->pageRenderer->renderErrorResponse(new ErrorPage('Fout', 'U heeft onvoldoende rechten om deze functie te gebruiken!', Response::HTTP_UNAUTHORIZED));
        }

        $id = $queryBits->getNullableInt(2);

        $class = $registry->editorSavePages[$type];
        try
        {
            $imageExtractor = new ImageExtractor(self::IMAGE_DIR);
            $dic->add($imageExtractor);

            /** @var EditorSavePage $editorSavePage */
            $editorSavePage = $dic->createClassWithDependencyInjection($class);
            $id = $editorSavePage->save($id);
            $editorSavePage->updateFriendlyUrl($urlService, $id, $post->getUrl('friendlyUrl'));
            $returnUrl = $editorSavePage->getReturnUrl() ?: '/';

            return new RedirectResponse($returnUrl);
        }
        catch (\PDOException $e)
        {
            $page = new SimplePage('Fout bij opslaan', $e->getFile() . ':' . $e->getLine() . ' ' . $e->getTraceAsString() . PHP_EOL . $e->getMessage());
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param class-string<Linkable>[] $internalLinkTypes
     * @return Link[]
     */
    private function getInternalLinks(array $internalLinkTypes, Connection $connection): array
    {
        /** @var Link[] $internalLinks */
        $internalLinks = [];
        foreach ($internalLinkTypes as $internalLinkType)
        {
            /** @var Linkable $class */
            $class = new $internalLinkType();
            $internalLinks = array_merge($internalLinks, $class->getList($connection));
        }
        usort($internalLinks, static function(Link $link1, Link $link2)
        {
            return $link1->name <=> $link2->name;
        });
        return $internalLinks;
    }
}
