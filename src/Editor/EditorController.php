<?php
declare(strict_types=1);

namespace Cyndaron\Editor;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\DBAL\Model;
use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\Error\ErrorPage;
use Cyndaron\Imaging\ImageExtractor;
use Cyndaron\Module\Datatype;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Page\Page;
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
use function usort;
use function property_exists;

final class EditorController
{
    private const IMAGE_DIR = Util::UPLOAD_DIR . '/images/via-editor';

    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly EditorPageRenderer $editorPageRenderer
    ) {

    }

    #[RouteAttribute('', RequestMethod::GET, UserLevel::LOGGED_IN)]
    public function routeGet(Request $request, QueryBits $queryBits, User $currentUser, ModuleRegistry $registry, GenericRepository $genericRepository, UserRepository $userRepository, DependencyInjectionContainer $dic): Response
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
        $datatype = $registry->datatypes[$type] ?? null;
        if ($datatype === null)
        {
            throw new \RuntimeException("Datatype {$type} not found!");
        }
        $id = $queryBits->getNullableInt(2);
        $previous = $queryBits->getString(3) === 'previous';
        $editorVariables = new EditorVariables($id, $previous, $this->getInternalLinks($registry->datatypes, $genericRepository), $datatype);
        $dic->add($editorVariables);

        /** @var EditorPage $editorPage */
        $editorPage = $dic->createClassWithDependencyInjection($class);
        return $this->editorPageRenderer->render($request, $queryBits, $editorPage, $editorVariables);
    }

    #[RouteAttribute('', RequestMethod::POST, UserLevel::LOGGED_IN)]
    public function routePost(QueryBits $queryBits, DependencyInjectionContainer $dic, RequestParameters $post, User $currentUser, ModuleRegistry $registry, UrlService $urlService, UserRepository $userRepository, FriendlyUrlHelper $friendlyUrlHelper): Response
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
            $savedModel = $editorSave->save($id);
            $returnUrl = $editorSave->getReturnUrl();
            if ($savedModel !== null)
            {
                $friendlyUrlHelper->updateFriendlyUrl($savedModel, $post->getUrl('friendlyUrl'));
                if ($returnUrl === '')
                {
                    $returnUrl = (string)$urlService->getFriendlyUrlForModel($savedModel);
                }
            }

            if (empty($returnUrl))
            {
                $returnUrl = '/';
            }

            return new RedirectResponse($returnUrl);
        }
        catch (\PDOException $e)
        {
            $page = Page::createSimple('Fout bij opslaan', $e->getFile() . ':' . $e->getLine() . ' ' . $e->getTraceAsString() . PHP_EOL . $e->getMessage());
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param array<string, Datatype> $modelToDatatypes
     * @return Link[]
     */
    private function getInternalLinks(array $modelToDatatypes, GenericRepository $genericRepository): array
    {
        /** @var Link[] $internalLinks */
        $internalLinks = [];
        foreach ($modelToDatatypes as $datatype)
        {
            /** @var class-string<Model> $modelClass */
            $modelClass = (string)$datatype->class;
            foreach ($genericRepository->fetchAll($modelClass) as $model)
            {
                if (!property_exists($model, 'name'))
                {
                    continue;
                }

                $closure = $datatype->modelToUrl;
                if ($closure === null)
                {
                    continue;
                }

                $url = $closure($model);
                $internalLinks[] = new Link((string)$url, "{$datatype->singular}: {$model->name}");
            }
        }
        usort($internalLinks, static function(Link $link1, Link $link2)
        {
            return $link1->name <=> $link2->name;
        });
        return $internalLinks;
    }
}
