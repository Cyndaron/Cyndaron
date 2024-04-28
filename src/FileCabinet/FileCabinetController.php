<?php
declare(strict_types=1);

namespace Cyndaron\FileCabinet;

use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\Controller;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Cyndaron\User\UserSession;
use Cyndaron\Util\Util;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use function basename;
use function move_uploaded_file;
use function file_exists;

final class FileCabinetController extends Controller
{
    #[RouteAttribute('', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function routeGet(): Response
    {
        $page = new OverviewPage();
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('addItem', RequestMethod::POST, UserLevel::ADMIN)]
    public function addItem(): Response
    {
        $filename = Util::UPLOAD_DIR . '/filecabinet/' . basename($_FILES['newFile']['name']);
        if (move_uploaded_file($_FILES['newFile']['tmp_name'], $filename))
        {
            UserSession::addNotification('Bestand geüpload');
        }
        else
        {
            UserSession::addNotification('Bestand kon niet naar de uploadmap worden verplaatst.');
        }

        return new RedirectResponse('/filecabinet');
    }

    #[RouteAttribute('deleteItem', RequestMethod::POST, UserLevel::ADMIN)]
    public function deleteItem(RequestParameters $post): Response
    {
        $filename = $post->getFilename('filename');
        $fullPath = Util::UPLOAD_DIR . "/filecabinet/$filename";
        if ($filename !== 'include.html' && file_exists($fullPath))
        {
            if (Util::deleteFile($fullPath))
            {
                UserSession::addNotification('Bestand verwijderd.');
            }
            else
            {
                UserSession::addNotification('Bestand kon niet worden verwijderd.');
            }
        }
        else
        {
            UserSession::addNotification('Bestand bestaat niet.');
        }

        return new RedirectResponse('/filecabinet');
    }
}
