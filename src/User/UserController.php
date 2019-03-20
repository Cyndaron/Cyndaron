<?php
declare (strict_types = 1);

namespace Cyndaron\User;

use Cyndaron\Controller;
use Cyndaron\Request;
use Cyndaron\Util;

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
            $page = new \Cyndaron\Page('Inloggen mislukt', $e->getMessage());
            $page->showPrePage();
            $page->showBody();
            $page->showPostPage();
        }
        catch (\Exception $e)
        {
            $page = new \Cyndaron\Page('Inloggen mislukt', 'Onbekende fout: ' . $e->getMessage());
            $page->showPrePage();
            $page->showBody();
            $page->showPostPage();
        }
    }

    protected function add()
    {
        $username = Request::post('username');
        $email = Request::post('email');
        $password = Request::post('password') ?: Util::generatePassword();
        $level = intval(Request::post('level'));
        $firstname = Request::post('firstname');
        $tussenvoegsel = Request::post('tussenvoegsel');
        $lastname = Request::post('lastname');
        $role = Request::post('role');
        $comments = Request::post('comments');
        $avatar = Request::post('avatar');
        $hideFromMemberList = Request::post('hideFromMemberList') == '1' ? true : false;

        $userId = User::create($username, $email, $password, $level, $firstname, $tussenvoegsel, $lastname, $role, $comments, $avatar, $hideFromMemberList);
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
            $firstname = Request::post('firstname');
            $tussenvoegsel = Request::post('tussenvoegsel');
            $lastname = Request::post('lastname');
            $role = Request::post('role');
            $comments = Request::post('comments');
            $avatar = Request::post('avatar');
            $hideFromMemberList = Request::post('hideFromMemberList') == '1' ? true : false;
            $this->editHelper(intval($id), $username, $email, $level, $firstname, $tussenvoegsel, $lastname, $role, $comments, $avatar, $hideFromMemberList);
        }
        else
        {
            $this->send400();
        }
    }

    private function editHelper(int $id, string $username, string $email, int $level, string $firstname, string $tussenvoegsel, string $lastname, string $role, string $comments, string $avatar, bool $hideFromMemberList)
    {
        $user = new User($id);
        $user->fetchRecord();
        $user->updateFromArray([
            'username' => $username,
            'email' => $email,
            'level' => $level,
            'firstname' => $firstname,
            'tussenvoegsel' => $tussenvoegsel,
            'lastname' => $lastname,
            'role' => $role,
            'comments' => $comments,
            'avatar' => $avatar,
            'hide_from_member_list' => intval($hideFromMemberList),
        ]);
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
            $user->fetchRecord();
            $user->sendNewPassword();

            echo json_encode([]);
        }
        else
        {
            $this->send400();
        }
    }
}