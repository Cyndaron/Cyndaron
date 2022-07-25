<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Cyndaron\Request\QueryBits;
use Cyndaron\Routing\Controller;
use Cyndaron\Menu\MenuItem;
use Cyndaron\View\Page;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserLevel;
use Cyndaron\View\SimplePage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use function count;

final class PhotoalbumController extends Controller
{
    protected array $postRoutes = [
        'addPhoto' => ['level' => UserLevel::ADMIN, 'function' => 'addPhoto'],
        'deletePhoto' => ['level' => UserLevel::ADMIN, 'function' => 'deletePhoto'],
    ];

    protected array $apiPostRoutes = [
        'add' => ['level' => UserLevel::ADMIN, 'function' => 'add'],
        'addtomenu' => ['level' => UserLevel::ADMIN, 'function' => 'addToMenu'],
        'delete' => ['level' => UserLevel::ADMIN, 'function' => 'delete'],
        'edit' => ['level' => UserLevel::ADMIN, 'function' => 'edit'],
    ];

    protected function routeGet(QueryBits $queryBits): Response
    {
        $id = $queryBits->getInt(1);
        if ($id < 1)
        {
            $page = new SimplePage('Fotoalbum', 'Ongeldige parameter.');
            return new Response($page->render(), Response::HTTP_BAD_REQUEST);
        }
        $album = Photoalbum::loadFromDatabase($id);
        if ($album === null)
        {
            return new JsonResponse(['error' => 'Album does not exist!'], Response::HTTP_NOT_FOUND);
        }
        $page = new PhotoalbumPage($album);
        return new Response($page->render());
    }

    public function add(RequestParameters $post): JsonResponse
    {
        $name = $post->getHTML('name');
        Photoalbum::create($name);

        return new JsonResponse();
    }

    public function addPhoto(QueryBits $queryBits): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }

        $album = Photoalbum::loadFromDatabase($id);
        if ($album === null)
        {
            throw new \Exception('Photo album not found!');
        }
        $numPhotos = count($_FILES['newFiles']['name']);
        for ($i = 0; $i < $numPhotos; $i++)
        {
            if (!$_FILES['newFiles']['error'][$i])
            {
                Photo::create($album, $_FILES['newFiles']['tmp_name'][$i], $_FILES['newFiles']['name'][$i]);
            }
        }

        return new RedirectResponse("/photoalbum/{$album->id}");
    }

    public function addToMenu(QueryBits $queryBits): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }

        $menuItem = new MenuItem();
        $menuItem->link = '/photoalbum/' . $id;
        $menuItem->save();

        return new JsonResponse();
    }

    public function delete(QueryBits $queryBits): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }

        $obj = new Photoalbum($id);
        $obj->delete();

        return new JsonResponse();
    }

    public function deletePhoto(QueryBits $queryBits): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }

        $album = Photoalbum::loadFromDatabase($id);
        if ($album === null)
        {
            throw new \Exception('Photo album not found!');
        }
        $filename = $queryBits->getString(3);
        if ($filename === '')
        {
            $page = new SimplePage('Fout bij verwijderen foto', 'Geen bestandsnaam opgegeven!');
            return new Response($page->render(), Response::HTTP_BAD_REQUEST);
        }
        $numDeleted = Photo::deleteByAlbumAndFilename($album, $filename);
        if ($numDeleted === 0)
        {
            $page = new SimplePage('Fout bij verwijderen foto', 'Kon de bestanden niet vinden!');
            return new Response($page->render(), Response::HTTP_BAD_REQUEST);
        }

        return new RedirectResponse("/photoalbum/{$album->id}");
    }

    public function edit(QueryBits $queryBits, RequestParameters $post): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }

        $album = Photoalbum::loadFromDatabase($id);
        if ($album === null)
        {
            throw new \Exception('Photoalbum not found!');
        }
        $album->name = $post->getHTML('name');
        $album->save();

        return new JsonResponse();
    }
}
