<?php
declare (strict_types = 1);

namespace Cyndaron\User;

use Cyndaron\Controller;
use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\Util;
use Exception;

class UserController extends Controller
{
    protected $getRoutes = [
        'gallery' => ['level' => UserLevel::LOGGED_IN, 'function' => 'gallery'],
        'login' => ['level' => UserLevel::ANONYMOUS, 'function' => 'loginGet'],
        'logout' => ['level' => UserLevel::LOGGED_IN, 'function' => 'logout'],
        'manager' => ['level' => UserLevel::ADMIN, 'function' => 'manager'],
    ];

    protected $postRoutes = [
        'add' => ['level' => UserLevel::ADMIN, 'function' => 'add'],
        'delete' => ['level' => UserLevel::ADMIN, 'function' => 'delete'],
        'edit' => ['level' => UserLevel::ADMIN, 'function' => 'edit'],
        'login' => ['level' => UserLevel::ANONYMOUS, 'function' => 'loginPost'],
        'resetpassword' => ['level' => UserLevel::ADMIN, 'function' => 'resetPassword'],
    ];

    protected function gallery()
    {
        new Gallery();
    }

    protected function loginGet()
    {
        if (empty($_SESSION['redirect']))
        {
            $_SESSION['redirect'] = Request::referrer();
        }
        new LoginPage();
    }

    protected function logout()
    {
        User::logout();
    }

    protected function manager()
    {
        new UserManagerPage();
    }

    protected function loginPost()
    {
        $identification = Request::post('login_user');
        $verification = Request::post('login_pass');

        try
        {
            User::login($identification, $verification);
        }
        catch (IncorrectCredentials $e)
        {
            $page = new Page('Inloggen mislukt', $e->getMessage());
            $page->render();
        }
        catch (Exception $e)
        {
            $page = new Page('Inloggen mislukt', 'Onbekende fout: ' . $e->getMessage());
            $page->render();
        }
    }

    protected function add()
    {
        $username = Request::post('username');
        $email = Request::post('email');
        $password = Request::post('password') ?: Util::generatePassword();
        $level = intval(Request::post('level'));
        $firstName = Request::post('firstName');
        $tussenvoegsel = Request::post('tussenvoegsel');
        $lastName = Request::post('lastName');
        $role = Request::post('role');
        $comments = Request::post('comments');
        $avatar = Request::post('avatar');
        $hideFromMemberList = Request::post('hideFromMemberList') == '1' ? true : false;

        $userId = User::create($username, $email, $password, $level, $firstName, $tussenvoegsel, $lastName, $role, $comments, $avatar, $hideFromMemberList);
        echo json_encode(['userId' => $userId]);
    }

    protected function edit()
    {
        $id = Request::getVar(2);
        if ($id !== null)
        {
            $username = Request::post('username');
            $email = Request::post('email');
            $level = intval(Request::post('level'));
            $firstName = Request::post('firstName');
            $tussenvoegsel = Request::post('tussenvoegsel');
            $lastName = Request::post('lastName');
            $role = Request::post('role');
            $comments = Request::post('comments');
            $avatar = Request::post('avatar');
            $hideFromMemberList = Request::post('hideFromMemberList') == '1' ? true : false;
            $this->editHelper(intval($id), $username, $email, $level, $firstName, $tussenvoegsel, $lastName, $role, $comments, $avatar, $hideFromMemberList);
        }
        else
        {
            $this->send400();
        }
    }

    private function editHelper(int $id, string $username, string $email, int $level, string $firstName, string $tussenvoegsel, string $lastName, string $role, string $comments, string $avatar, bool $hideFromMemberList)
    {
        $user = new User($id);
        $user->load();
        $user->username = $username;
        $user->email = $email;
        $user->level = $level;
        $user->firstName = $firstName;
        $user->tussenvoegsel = $tussenvoegsel;
        $user->lastName = $lastName;
        $user->role = $role;
        $user->comments = $comments;
        $user->avatar = $avatar;
        $user->hideFromMemberList = intval($hideFromMemberList);
        $result = $user->save();
        if ($result !== true)
        {
            $this->send500('Could not update user!');
        }
    }

    protected function delete()
    {
        $userId = Request::getVar(2);
        if ($userId !== null)
        {
            $user = new User($userId);
            $user->delete();

            echo json_encode([]);
        }
        else
        {
            $this->send400();
        }
    }

    protected function resetPassword()
    {
        $userId = Request::getVar(2);
        if ($userId !== null)
        {
            $user = new User($userId);
            $user->load();
            $user->sendNewPassword();

            echo json_encode([]);
        }
        else
        {
            $this->send400();
        }
    }
}