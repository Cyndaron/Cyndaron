<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\Menu\MenuItem;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\Controller;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Cyndaron\User\UserSession;
use Cyndaron\View\Renderer\TextRenderer;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function base64_decode;
use function pathinfo;
use function assert;
use function is_array;

final class PhotoalbumController extends Controller
{
    #[RouteAttribute('', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function routeGet(QueryBits $queryBits, TextRenderer $textRenderer, UserSession $session): Response
    {
        $id = $queryBits->getInt(1);
        if ($id < 1)
        {
            $page = new SimplePage('Fotoalbum', 'Ongeldige parameter.');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_BAD_REQUEST);
        }
        $album = Photoalbum::fetchById($id);
        if ($album === null)
        {
            return new JsonResponse(['error' => 'Album does not exist!'], Response::HTTP_NOT_FOUND);
        }
        $page = new PhotoalbumPage($album, $textRenderer, $session->getProfile());
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('add', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: Photoalbum::RIGHT_EDIT)]
    public function add(RequestParameters $post): JsonResponse
    {
        $name = $post->getHTML('name');
        Photoalbum::create($name);

        return new JsonResponse();
    }

    private function addPhotoCommon(Photoalbum $album, FileBag $fileBag): void
    {
        $files = $fileBag->get('newFiles');
        assert(is_array($files) && $files[0] instanceof UploadedFile);
        foreach ($files as $file)
        {
            if ($file->isValid())
            {
                $tempName = $file->getPathname();
                $originalName = $file->getClientOriginalName();
                $proposedName = pathinfo($originalName, PATHINFO_FILENAME) . '.webp';
                Photo::create($album, $tempName, $proposedName);
            }
        }
    }

    #[RouteAttribute('addPhoto', RequestMethod::POST, UserLevel::ADMIN, right: Photoalbum::RIGHT_UPLOAD)]
    public function addPhoto(QueryBits $queryBits, Request $request): Response
    {
        $id = $queryBits->getInt(2);
        $album = Photoalbum::fetchById($id);
        if ($album === null)
        {
            throw new \Exception('Photo album not found!');
        }

        $this->addPhotoCommon($album, $request->files);

        return new RedirectResponse("/photoalbum/{$album->id}");
    }

    #[RouteAttribute('addPhoto', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: Photoalbum::RIGHT_UPLOAD)]
    public function addPhotoApi(QueryBits $queryBits, Request $request): JsonResponse
    {
        $id = $queryBits->getInt(2);
        $album = Photoalbum::fetchById($id);
        if ($album === null)
        {
            return new JsonResponse(['error' => 'Photo album not found!'], Response::HTTP_NOT_FOUND);
        }

        $this->addPhotoCommon($album, $request->files);

        return new JsonResponse([]);
    }

    #[RouteAttribute('addtomenu', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function addToMenu(QueryBits $queryBits, GenericRepository $repository): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }

        $menuItem = new MenuItem();
        $menuItem->link = '/photoalbum/' . $id;
        $repository->save($menuItem);

        return new JsonResponse();
    }

    #[RouteAttribute('delete', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function delete(QueryBits $queryBits, GenericRepository $repository): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }

        $repository->deleteById(Photoalbum::class, $id);

        return new JsonResponse();
    }

    #[RouteAttribute('deletePhoto', RequestMethod::POST, UserLevel::ADMIN)]
    public function deletePhoto(QueryBits $queryBits): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }

        $album = Photoalbum::fetchById($id);
        if ($album === null)
        {
            throw new \Exception('Photo album not found!');
        }
        $filename = $queryBits->getString(3);
        if ($filename === '')
        {
            $page = new SimplePage('Fout bij verwijderen foto', 'Geen bestandsnaam opgegeven!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_BAD_REQUEST);
        }
        $filename = base64_decode($filename, true);
        if ($filename === false)
        {
            $page = new SimplePage('Fout bij verwijderen foto', 'Bestandsnaam onjuist gecodeerd!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_BAD_REQUEST);
        }

        $numDeleted = Photo::deleteByAlbumAndFilename($album, $filename);
        if ($numDeleted === 0)
        {
            $page = new SimplePage('Fout bij verwijderen foto', 'Kon de bestanden niet vinden!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_BAD_REQUEST);
        }

        return new RedirectResponse("/photoalbum/{$album->id}");
    }

    #[RouteAttribute('edit', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: Photoalbum::RIGHT_EDIT)]
    public function edit(QueryBits $queryBits, RequestParameters $post): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }

        $album = Photoalbum::fetchById($id);
        if ($album === null)
        {
            throw new \Exception('Photoalbum not found!');
        }
        $album->name = $post->getHTML('name');
        $album->save();

        return new JsonResponse();
    }
}
