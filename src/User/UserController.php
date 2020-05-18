<?php
declare (strict_types = 1);

namespace Cyndaron\User;

use Cyndaron\Controller;
use Cyndaron\Page;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Router;
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
            $_SESSION['redirect'] = Router::referrer();
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

    protected function loginPost(RequestParameters $post): Response
    {
        $identification = $post->getAlphaNum('login_user');
        $verification = $post->getUnfilteredString('login_pass');

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

    protected function add(RequestParameters $post): JsonResponse
    {
        $username = $post->getAlphaNum('username');
        $email = $post->getEmail('email');
        $password = $post->getUnfilteredString('password') ?: Util::generatePassword();
        $level = $post->getInt('level');
        $firstName = $post->getSimpleString('firstName');
        $tussenvoegsel = $post->getTussenvoegsel('tussenvoegsel');
        $lastName = $post->getSimpleString('lastName');
        $role = $post->getSimpleString('role');
        $comments = $post->getHTML('comments');
        $avatar = $post->getFilenameWithDirectory('avatar');
        $hideFromMemberList = $post->getBool('hideFromMemberList');

        $userId = User::create($username, $email, $password, $level, $firstName, $tussenvoegsel, $lastName, $role, $comments, $avatar, $hideFromMemberList);
        return new JsonResponse(['userId' => $userId]);
    }

    protected function edit(RequestParameters $post): JsonResponse
    {
        $id = $this->queryBits->getInt(2);
        if ($id === null)
        {
            return new JsonResponse(['error' => 'No ID specified!', Response::HTTP_BAD_REQUEST]);
        }

        $user = User::loadFromDatabase($id);
        if ($user === null)
        {
            return new JsonResponse(['error' => 'User not found!', Response::HTTP_NOT_FOUND]);
        }

        $user->username = $post->getAlphaNum('username');
        $user->email = $post->getEmail('email');
        $user->level = $post->getInt('level');
        $user->firstName = $post->getSimpleString('firstName');
        $user->tussenvoegsel = $post->getTussenvoegsel('tussenvoegsel');
        $user->lastName = $post->getSimpleString('lastName');
        $user->role = $post->getSimpleString('role');
        $user->comments = $post->getHTML('comments');
        $user->avatar = $post->getFilenameWithDirectory('avatar');
        $user->hideFromMemberList = $post->getBool('hideFromMemberList');
        $result = $user->save();
        if ($result === false)
        {
            return new JsonResponse(['error' => 'Could not update user!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse();
    }

    protected function delete(): JsonResponse
    {
        $userId = $this->queryBits->getInt(2);
        if ($userId === null)
        {
            return new JsonResponse(['error' => 'No ID specified!'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User($userId);
        $user->delete();

        return new JsonResponse();
    }

    protected function resetPassword(): JsonResponse
    {
        $userId = $this->queryBits->getInt(2);
        if ($userId === null)
        {
            return new JsonResponse(['error' => 'ID not specified!'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User($userId);
        $user->load();
        $user->resetPassword();

        return new JsonResponse();
    }

    protected function changeAvatar(): Response
    {
        $userId = $this->queryBits->getInt(2);
        if ($userId === null)
        {
            $page = new Page('Fout bij veranderen avatar', 'Onbekende fout.');
            return new Response($page->render(), Response::HTTP_BAD_REQUEST);
        }

        $user = new User($userId);
        $user->load();
        $user->uploadNewAvatar();

        return new RedirectResponse('/user/manager');
    }
}