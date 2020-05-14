<?php
declare (strict_types = 1);

namespace Cyndaron\User;

use Cyndaron\Controller;
use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\Setting;
use Cyndaron\Util;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    protected function gallery(): Response
    {
        // Has to be done here because you cannot specify the expression during member variable initialization.
        $minLevel = (int)Setting::get('userGalleryMinLevel') ?: UserLevel::ADMIN;
        $this->checkUserLevelOrDie($minLevel);
        $page = new Gallery();
        return new Response($page->render());
    }

    protected function loginGet(): Response
    {
        if (empty($_SESSION['redirect']))
        {
            $_SESSION['redirect'] = Request::referrer();
        }
        $page = new LoginPage();
        return new Response($page->render());
    }

    protected function logout(): Response
    {
        User::logout();
        return new RedirectResponse('/');
    }

    protected function manager(): Response
    {
        $page = new UserManagerPage();
        return new Response($page->render());
    }

    protected function loginPost(): Response
    {
        $identification = Request::post('login_user');
        $verification = Request::post('login_pass');

        try
        {
            $redirectUrl = User::login($identification, $verification);
            return new RedirectResponse($redirectUrl);
        }
        catch (IncorrectCredentials $e)
        {
            $page = new Page('Inloggen mislukt', $e->getMessage());
            return new Response($page->render());
        }
        catch (Exception $e)
        {
            $page = new Page('Inloggen mislukt', 'Onbekende fout: ' . $e->getMessage());
            return new Response($page->render());
        }
    }

    protected function add(): JsonResponse
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
        return new JsonResponse(['userId' => $userId]);
    }

    protected function edit(): JsonResponse
    {
        $id = Request::getVar(2);
        if ($id === null)
        {
            return new JsonResponse(['error' => 'No ID specified!', Response::HTTP_BAD_REQUEST]);
        }

        $user = User::loadFromDatabase((int)$id);
        if ($user === null)
        {
            return new JsonResponse(['error' => 'User not found!', Response::HTTP_NOT_FOUND]);
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
            return new JsonResponse(['error' => 'Could not update user!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse();
    }

    protected function delete(): JsonResponse
    {
        $userId = Request::getVar(2);
        if ($userId === null)
        {
            return new JsonResponse(['error' => 'No ID specified!'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User((int)$userId);
        $user->delete();

        return new JsonResponse();
    }

    protected function resetPassword(): JsonResponse
    {
        $userId = Request::getVar(2);
        if ($userId === null)
        {
            return new JsonResponse(['error' => 'ID not specified!'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User((int)$userId);
        $user->load();
        $user->resetPassword();

        return new JsonResponse();
    }

    protected function changeAvatar()
    {
        $userId = Request::getVar(2);
        if ($userId !== null)
        {
            $user = new User((int)$userId);
            $user->load();
            $user->uploadNewAvatar();

            return new RedirectResponse('/user/manager');
        }
        else
        {
            $page = new Page('Fout bij veranderen avatar', 'Onbekende fout.');
            return new Response($page->render(), Response::HTTP_BAD_REQUEST);
        }
    }
}