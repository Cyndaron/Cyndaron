<?php
declare (strict_types = 1);

namespace Cyndaron\User;

use Cyndaron\Controller;
use Cyndaron\Request;
use Cyndaron\Util;

class UserController extends Controller
{
    /* In order to allow users to login and modify their own data. Add the appropriate User::isAdmin() checks where needed. */
    protected $minLevelPost = UserLevel::ANONYMOUS;

    public function routeGet()
    {
        switch ($this->action)
        {
            case 'login':
                if (empty($_SESSION['redirect']))
                {
                    $_SESSION['redirect'] = Request::referrer();
                }
                new LoginPage();
                break;
            case 'logout':
                User::logout();
                break;
            case 'gallery':
                new Gallery();
                break;
            case 'manager':
                if (User::isAdmin())
                    new UserManagerPage();
                break;
        }
    }

    public function routePost()
    {
        if (!User::isLoggedIn())
        {
            switch ($this->action)
            {
                case 'login':
                    $this->login();
                    break;
                default:
                    $this->send401();
            }
        }
        else
        {
            switch ($this->action)
            {
                case 'add':
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

                    $this->create($username, $email, $password, $level, $firstname, $tussenvoegsel, $lastname, $role, $comments, $avatar, $hideFromMemberList);
                    break;
                case 'edit':
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
                        $this->edit(intval($id), $username, $email, $level, $firstname, $tussenvoegsel, $lastname, $role, $comments, $avatar, $hideFromMemberList);
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
    }

    private function login()
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

    public function create(string $username, string $email, string $password, int $level, string $firstname, string $tussenvoegsel, string $lastname, string $role, string $comments, string $avatar, bool $hideFromMemberList)
    {
        if (!User::isAdmin())
        {
            $this->send401();
            return;
        }
        $userId = User::create($username, $email, $password, $level, $firstname, $tussenvoegsel, $lastname, $role, $comments, $avatar, $hideFromMemberList);

        echo json_encode(['userId' => $userId]);
    }

    public function edit(int $id, string $username, string $email, int $level, string $firstname, string $tussenvoegsel, string $lastname, string $role, string $comments, string $avatar, bool $hideFromMemberList)
    {
        if (!User::isAdmin())
        {
            $this->send401();
            return;
        }

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