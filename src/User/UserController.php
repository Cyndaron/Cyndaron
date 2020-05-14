<?php
declare (strict_types = 1);

namespace Cyndaron\User;

use Cyndaron\Controller;
use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\Response\JSONResponse;
use Cyndaron\Setting;
use Cyndaron\Util;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    protected array $getRoutes = [
        'gallery' => ['level' => UserLevel::LOGGED_IN, 'function' => 'gallery'],
        'login' => ['level' => UserLevel::ANONYMOUS, 'function' => 'loginGet'],
        'logout' => ['level' => UserLevel::LOGGED_IN, 'function' => 'logout'],
        'manager' => ['level' => UserLevel::ADMIN, 'function' => 'manager'],
    ];

    protected array $postRoutes = [
        'login' => ['level' => UserLevel::ANONYMOUS, 'function' => 'loginPost'],
        'changeAvatar' => ['level' => UserLevel::ADMIN, 'function' => 'changeAvatar'],
    ];

    protected array $apiPostRoutes = [
        'add' => ['level' => UserLevel::ADMIN, 'function' => 'add'],
        'edit' => ['level' => UserLevel::ADMIN, 'function' => 'edit'],
        'delete' => ['level' => UserLevel::ADMIN, 'function' => 'delete'],
        'resetpassword' => ['level' => UserLevel::ADMIN, 'function' => 'resetPassword'],
    ];

    protected function gallery()
    {
        // Has to be done here because you cannot specify the expression during member variable initialization.
        $minLevel = (int)Setting::get('userGalleryMinLevel') ?: UserLevel::ADMIN;
        $this->checkUserLevelOrDie($minLevel);
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
            $page->renderAndEcho();
        }
        catch (Exception $e)
        {
            $page = new Page('Inloggen mislukt', 'Onbekende fout: ' . $e->getMessage());
            $page->renderAndEcho();
        }
    }

    protected function add(): JSONResponse
    {
        $username = Request::post('username');
        $email = Request::post('email');
        $password = Request::post('password') ?: Util::generatePassword();
        $level = (int)Request::post('level');
        $firstName = Request::post('firstName');
        $tussenvoegsel = Request::post('tussenvoegsel');
        $lastName = Request::post('lastName');
        $role = Request::post('role');
        $comments = Request::post('comments');
        $avatar = Request::post('avatar');
        $hideFromMemberList = Request::post('hideFromMemberList') === '1';

        $userId = User::create($username, $email, $password, $level, $firstName, $tussenvoegsel, $lastName, $role, $comments, $avatar, $hideFromMemberList);
        return new JSONResponse(['userId' => $userId]);
    }

    protected function edit(): JSONResponse
    {
        $id = Request::getVar(2);
        if ($id === null)
        {
            return new JSONResponse(['error' => 'No ID specified!', Response::HTTP_BAD_REQUEST]);
        }

        $user = User::loadFromDatabase((int)$id);
        if ($user === null)
        {
            return new JSONResponse(['error' => 'User not found!', Response::HTTP_NOT_FOUND]);
        }

        $user->username = Request::post('username');
        $user->email = Request::post('email');
        $user->level = (int)Request::post('level');
        $user->firstName = Request::post('firstName');
        $user->tussenvoegsel = Request::post('tussenvoegsel');
        $user->lastName = Request::post('lastName');
        $user->role = Request::post('role');
        $user->comments = Request::post('comments');
        $user->avatar = Request::post('avatar');
        $user->hideFromMemberList = Request::post('hideFromMemberList') === '1';
        $result = $user->save();
        if ($result === false)
        {
            return new JSONResponse(['error' => 'Could not update user!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JSONResponse();
    }

    protected function delete(): JSONResponse
    {
        $userId = Request::getVar(2);
        if ($userId === null)
        {
            return new JSONResponse(['error' => 'No ID specified!'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User((int)$userId);
        $user->delete();

        return new JSONResponse();
    }

    protected function resetPassword(): JSONResponse
    {
        $userId = Request::getVar(2);
        if ($userId === null)
        {
            return new JSONResponse(['error' => 'ID not specified!'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User((int)$userId);
        $user->load();
        $user->resetPassword();

        return new JSONResponse();
    }

    protected function changeAvatar()
    {
        $userId = Request::getVar(2);
        if ($userId !== null)
        {
            $user = new User((int)$userId);
            $user->load();
            $user->uploadNewAvatar();

            header('Location: /user/manager');
        }
        else
        {
            $this->send400();
        }
    }
}