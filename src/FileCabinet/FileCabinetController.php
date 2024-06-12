<?php
declare(strict_types=1);

namespace Cyndaron\FileCabinet;

use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\Controller;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\User\UserLevel;
use Cyndaron\User\UserSession;
use Cyndaron\Util\Util;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function basename;
use function file_exists;
use function assert;

final class FileCabinetController extends Controller
{
    #[RouteAttribute('', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function routeGet(CSRFTokenHandler $tokenHandler): Response
    {
        $page = new OverviewPage($tokenHandler);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('addItem', RequestMethod::POST, UserLevel::ADMIN)]
    public function addItem(Request $request, UserSession $userSession): Response
    {
        $file = $request->files->get('newFile');
        assert($file instanceof UploadedFile);
        $filename = basename($file->getClientOriginalName());
        try
        {
            $file->move(Util::UPLOAD_DIR . '/filecabinet', $filename);
            $userSession->addNotification('Bestand geüpload');
        }
        catch (FileException)
        {
            $userSession->addNotification('Bestand kon niet naar de uploadmap worden verplaatst.');
        }

        return new RedirectResponse('/filecabinet');
    }

    #[RouteAttribute('deleteItem', RequestMethod::POST, UserLevel::ADMIN)]
    public function deleteItem(RequestParameters $post, UserSession $userSession): Response
    {
        $filename = $post->getFilename('filename');
        $fullPath = Util::UPLOAD_DIR . "/filecabinet/$filename";
        if ($filename !== 'include.html' && file_exists($fullPath))
        {
            if (Util::deleteFile($fullPath))
            {
                $userSession->addNotification('Bestand verwijderd.');
            }
            else
            {
                $userSession->addNotification('Bestand kon niet worden verwijderd.');
            }
        }
        else
        {
            $userSession->addNotification('Bestand bestaat niet.');
        }

        return new RedirectResponse('/filecabinet');
    }
}
