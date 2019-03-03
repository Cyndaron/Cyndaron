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
                    $_SESSION['redirect'] = Request::geefReferrerVeilig();
                }
                new LoginPage();
                break;
            case 'logout':
                User::logout();
                break;
            case 'gallery':
                new Gallery();
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
                    $username = Request::geefPostVeilig('username');
                    $email = Request::geefPostVeilig('email');
                    $password = Request::geefPostVeilig('password') ?: Util::generatePassword();
                    $level = intval(Request::geefPostVeilig('level'));
                    $firstname = Request::geefPostVeilig('firstname');
                    $tussenvoegsel = Request::geefPostVeilig('tussenvoegsel');
                    $lastname = Request::geefPostVeilig('lastname');
                    $role = Request::geefPostVeilig('role');
                    $comments = Request::geefPostVeilig('comments');
                    $avatar = Request::geefPostVeilig('avatar');
                    $hideFromMemberList = Request::geefPostVeilig('hideFromMemberList') == '1' ? true : false;

                    $this->create($username, $email, $password, $level, $firstname, $tussenvoegsel, $lastname, $role, $comments, $avatar, $hideFromMemberList);
                    break;
                case 'edit':
                    $id = Request::getVar(2);
                    if ($id !== null)
                    {
                        $username = Request::geefPostVeilig('username');
                        $email = Request::geefPostVeilig('email');
                        $level = intval(Request::geefPostVeilig('level'));
                        $firstname = Request::geefPostVeilig('firstname');
                        $tussenvoegsel = Request::geefPostVeilig('tussenvoegsel');
                        $lastname = Request::geefPostVeilig('lastname');
                        $role = Request::geefPostVeilig('role');
                        $comments = Request::geefPostVeilig('comments');
                        $avatar = Request::geefPostVeilig('avatar');
                        $hideFromMemberList = Request::geefPostVeilig('hideFromMemberList') == '1' ? true : false;
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
        $identification = Request::geefPostVeilig('login_user');
        $verification = Request::geefPostVeilig('login_pass');

        try
        {
            User::login($identification, $verification);
        }
        catch (IncorrectCredentials $e)
        {
            $page = new \Cyndaron\Pagina('Inloggen mislukt', $e->getMessage());
            $page->showPrePage();
            $page->showBody();
            $page->showPostPage();
        }
        catch (\Exception $e)
        {
            $page = new \Cyndaron\Pagina('Inloggen mislukt', 'Onbekende fout: ' . $e->getMessage());
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
            'gebruikersnaam' => $username,
            'email' => $email,
            'niveau' => $level,
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