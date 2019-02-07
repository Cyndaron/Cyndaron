<?php
declare(strict_types = 1);

namespace Cyndaron\User;

use Cyndaron\Controller;
use Cyndaron\Request;

class UserController extends Controller
{
    public function routePost()
    {
        $action = Request::getVar(1);

        switch ($action)
        {
            case 'resetpassword':
                $userId = Request::getVar(2);
                if ($userId !== null)
                    $this->resetPassword(intval($userId));
        }
    }

    public function resetPassword(int $userId): void
    {
        if (!User::isAdmin())
        {
            $this->send401();
            return;
        }
        $user = new User($userId);
        $user->fetchRecord();
        $user->sendNewPassword();

        echo json_encode([]);
    }
}