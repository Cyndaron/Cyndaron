<?php
declare(strict_types=1);

namespace Cyndaron\FileCabinet;

use Cyndaron\Request\QueryBits;
use Cyndaron\Routing\Controller;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\Util;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use function basename;
use function move_uploaded_file;
use function file_exists;

final class FileCabinetController extends Controller
{
    protected array $getRoutes = [
        '' => ['level' => UserLevel::ANONYMOUS, 'function' => 'routeGet'],
    ];

    protected array $postRoutes = [
        'addItem' => ['level' => UserLevel::ADMIN, 'function' => 'addItem'],
        'deleteItem' => ['level' => UserLevel::ADMIN, 'function' => 'deleteItem']
    ];

    protected function routeGet(QueryBits $queryBits): Response
    {
        $page = new OverviewPage();
        return new Response($page->render());
    }

    protected function addItem(): Response
    {
        $filename = Util::UPLOAD_DIR . '/filecabinet/' . basename($_FILES['newFile']['name']);
        if (move_uploaded_file($_FILES['newFile']['tmp_name'], $filename))
        {
            User::addNotification('Bestand geüpload');
        }
        else
        {
            User::addNotification('Bestand kon niet naar de uploadmap worden verplaatst.');
        }

        return new RedirectResponse('/filecabinet');
    }

    protected function deleteItem(RequestParameters $post): Response
    {
        $filename = $post->getFilename('filename');
        $fullPath = Util::UPLOAD_DIR . "/filecabinet/$filename";
        if ($filename !== 'include.html' && file_exists($fullPath))
        {
            if (Util::deleteFile($fullPath))
            {
                User::addNotification('Bestand verwijderd.');
            }
            else
            {
                User::addNotification('Bestand kon niet worden verwijderd.');
            }
        }
        else
        {
            User::addNotification('Bestand bestaat niet.');
        }

        return new RedirectResponse('/filecabinet');
    }
}
