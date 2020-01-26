<?php
declare (strict_types = 1);

namespace Cyndaron\FileCabinet;

use Cyndaron\Controller;
use Cyndaron\Request;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;

class FileCabinetController extends Controller
{
    protected array $postRoutes = [
        'addItem' => ['level' => UserLevel::ADMIN, 'function' => 'addItem'],
        'deleteItem' => ['level' => UserLevel::ADMIN, 'function' => 'deleteItem']
    ];

    protected function routeGet()
    {
        new OverviewPage();
    }

    protected function addItem()
    {
        $filename = './bestandenkast/' . basename($_FILES['newFile']['name']);
        if (move_uploaded_file($_FILES['newFile']['tmp_name'], $filename))
        {
            User::addNotification('Bestand geüpload');
        }
        else
        {
            User::addNotification('Bestand kon niet naar de uploadmap worden verplaatst.');
        }

        header('Location: /filecabinet');
    }

    protected function deleteItem()
    {
        $filename = Request::post('filename');
        $fullPath = "./bestandenkast/$filename";
        if ($filename !== 'include.html' && file_exists($fullPath))
        {
            if (@unlink($fullPath))
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

        header('Location: /filecabinet');
    }
}