<?php
declare (strict_types = 1);

namespace Cyndaron\User;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Request;
use Cyndaron\Util;

class UserController extends Controller
{
    /* In order to allow users to modify their own data. Add the appropriate User::isAdmin() checks where needed. */
    protected $minLevelPost = UserLevel::LOGGED_IN;

    public function routePost()
    {
        switch ($this->action)
        {
            case 'add':
                $username = Request::geefPostVeilig('username');
                $email = Request::geefPostVeilig('email');
                $password = Request::geefPostVeilig('password') ?: Util::generatePassword();
                $level = intval(Request::geefPostVeilig('level'));
                $this->create($username, $email, $password, $level);
                break;
            case 'edit':
                $id = Request::getVar(2);
                if ($id !== null)
                {
                    $username = Request::geefPostVeilig('username');
                    $email = Request::geefPostVeilig('email');
                    $level = intval(Request::geefPostVeilig('level'));
                    $this->edit(intval($id), $username, $email, $level);
                }
                break;
            case 'delete':
                $userId = Request::getVar(2);
                if ($userId !== null)
                    $this->delete(intval($userId));
                break;
            case 'resetpassword':
                $userId = Request::getVar(2);
                if ($userId !== null)
                    $this->resetPassword(intval($userId));
                break;
            default:
                $this->send404('Action not found!');
        }
    }

    public function create(string $username, string $email, string $password, int $level)
    {
        if (!User::isAdmin())
        {
            $this->send401();
            return;
        }
        $userId = User::create($username, $email, $password, $level);

        echo json_encode(['userId' => $userId]);
    }

    public function edit(int $id, string $username, string $email, int $level)
    {
        if (!User::isAdmin())
        {
            $this->send401();
            return;
        }

        $user = new User($id);
        $user->fetchRecord();
        $user->updateFromArray([
            'gebruikersnaam' => $username,
            'email' => $email,
            'niveau' => $level,
        ]);
        $result = $user->save();
        if ($result !== true)
        {
            $this->send500('Could not update user!');
        }
    }

    public function delete(int $userId): void
    {
        if (!User::isAdmin())
        {
            $this->send401();
            return;
        }
        $user = new User($userId);
        $user->delete();

        echo json_encode([]);
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