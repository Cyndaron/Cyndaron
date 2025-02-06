<?php
declare(strict_types=1);

namespace Cyndaron\Editor;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\DBAL\Connection;
use Cyndaron\Error\ErrorPage;
use Cyndaron\FriendlyUrl\FriendlyUrlRepository;
use Cyndaron\Imaging\ImageExtractor;
use Cyndaron\Module\Linkable;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Url\UrlService;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Cyndaron\User\UserRepository;
use Cyndaron\Util\DependencyInjectionContainer;
use Cyndaron\Util\Link;
use Cyndaron\Util\Util;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function array_key_exists;
use function array_merge;
use function strlen;
use function usort;

final class EditorController
{
    private const IMAGE_DIR = Util::UPLOAD_DIR . '/images/via-editor';

    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly EditorPageRenderer $editorPageRenderer
    ) {

    }

    #[RouteAttribute('', RequestMethod::GET, UserLevel::LOGGED_IN)]
    public function routeGet(Request $request, QueryBits $queryBits, User $currentUser, ModuleRegistry $registry, Connection $connection, UserRepository $userRepository, DependencyInjectionContainer $dic): Response
    {
        $type = $queryBits->getString(1);
        if (!array_key_exists($type, $registry->editorPages))
        {
            throw new \Exception('Onbekend paginatype: ' . $type);
        }
        if (!$userRepository->userHasRight($currentUser, "{$type}_edit"))
        {
            return $this->pageRenderer->renderErrorResponse(new ErrorPage('Fout', 'U heeft onvoldoende rechten om deze functie te gebruiken!', Response::HTTP_UNAUTHORIZED));
        }

        /** @var class-string<EditorPage> $class */
        $class = $registry->editorPages[$type];
        $id = $queryBits->getNullableInt(2);
        $previous = $queryBits->getString(3) === 'previous';
        $editorVariables = new EditorVariables($id, $previous, $this->getInternalLinks($registry->internalLinkTypes, $connection));
        $dic->add($editorVariables);

        /** @var EditorPage $editorPage */
        $editorPage = $dic->createClassWithDependencyInjection($class);
        return $this->editorPageRenderer->render($request, $queryBits, $editorPage, $editorVariables);
    }

    #[RouteAttribute('', RequestMethod::POST, UserLevel::LOGGED_IN)]
    public function routePost(QueryBits $queryBits, DependencyInjectionContainer $dic, RequestParameters $post, User $currentUser, ModuleRegistry $registry, UrlService $urlService, UserRepository $userRepository, Connection $connection, FriendlyUrlRepository $friendlyUrlRepository): Response
    {
        $type = $queryBits->getString(1);
        if (!array_key_exists($type, $registry->editorSaveClasses))
        {
            throw new \Exception('Onbekend paginatype: ' . $type);
        }
        if (!$userRepository->userHasRight($currentUser, "{$type}_edit"))
        {
            return $this->pageRenderer->renderErrorResponse(new ErrorPage('Fout', 'U heeft onvoldoende rechten om deze functie te gebruiken!', Response::HTTP_UNAUTHORIZED));
        }

        $id = $queryBits->getNullableInt(2);

        $class = $registry->editorSaveClasses[$type];
        try
        {
            $imageExtractor = new ImageExtractor(self::IMAGE_DIR);
            $dic->add($imageExtractor);

            /** @var EditorSave $editorSave */
            $editorSave = $dic->createClassWithDependencyInjection($class);
            $id = $editorSave->save($id);
            $editorSave->updateFriendlyUrl($urlService, $connection, $friendlyUrlRepository, $id, $post->getUrl('friendlyUrl'));
            $returnUrl = $editorSave->getReturnUrl() ?: '/';

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
