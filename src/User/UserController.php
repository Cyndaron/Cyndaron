<?php
declare(strict_types=1);

namespace Cyndaron\User;

use Cyndaron\Geelhoed\Contest\Contest;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\Controller;
use Cyndaron\Routing\Kernel;
use Cyndaron\Util\Setting;
use Cyndaron\Util\Util;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function strlen;

final class UserController extends Controller
{
    public array $getRoutes = [
        'changePassword' => ['level' => UserLevel::LOGGED_IN, 'function' => 'changePasswordGet'],
        'gallery' => ['level' => UserLevel::LOGGED_IN, 'function' => 'gallery'],
        'login' => ['level' => UserLevel::ANONYMOUS, 'function' => 'loginGet'],
        'logout' => ['level' => UserLevel::LOGGED_IN, 'function' => 'logout'],
        'manager' => ['level' => UserLevel::ADMIN, 'function' => 'manager'],
    ];

    public array $postRoutes = [
        'changePassword' => ['level' => UserLevel::LOGGED_IN, 'function' => 'changePasswordPost'],
        'login' => ['level' => UserLevel::ANONYMOUS, 'function' => 'loginPost'],
        'changeAvatar' => ['level' => UserLevel::ADMIN, 'function' => 'changeAvatar'],
    ];

    public array $apiPostRoutes = [
        'add' => ['level' => UserLevel::ADMIN, 'function' => 'add'],
        'edit' => ['level' => UserLevel::ADMIN, 'function' => 'edit'],
        'delete' => ['level' => UserLevel::ADMIN, 'function' => 'delete'],
        'resetpassword' => ['level' => UserLevel::ADMIN, 'right' => Contest::RIGHT_MANAGE, 'function' => 'resetPassword'],
    ];

    protected function gallery(User|null $currentUser): Response
    {
        // Has to be done here because you cannot specify the expression during member variable initialization.
        $minLevel = (int)Setting::get('userGalleryMinLevel') ?: UserLevel::ADMIN;
        $userLevel = $currentUser !== null ? $currentUser->level : UserLevel::ANONYMOUS;
        if ($userLevel < $minLevel)
        {
            $page = new SimplePage('Fout', 'U heeft onvoldoende rechten om deze pagina te bekijken.');
            return new Response($page->render(), Response::HTTP_UNAUTHORIZED);
        }

        $page = new Gallery();
        return new Response($page->render());
    }

    protected function loginGet(): Response
    {
        if (empty($_SESSION['redirect']))
        {
            $_SESSION['redirect'] = Kernel::referrer();
        }
        $page = new LoginPage();
        return new Response($page->render());
    }

    protected function logout(): Response
    {
        User::logout();
        return new RedirectResponse('/', Response::HTTP_FOUND, Kernel::HEADERS_DO_NOT_CACHE);
    }

    protected function manager(): Response
    {
        $page = new UserManagerPage();
        return new Response($page->render());
    }

    protected function loginPost(RequestParameters $post): Response
    {
        $identification = $post->getEmail('login_user');
        $verification = $post->getUnfilteredString('login_pass');

        try
        {
            $redirectUrl = User::login($identification, $verification);
            return new RedirectResponse($redirectUrl);
        }
        catch (IncorrectCredentials $e)
        {
            $page = new SimplePage('Inloggen mislukt', $e->getMessage());
            return new Response($page->render());
        }
        catch (Exception $e)
        {
            $page = new SimplePage('Inloggen mislukt', 'Onbekende fout: ' . $e->getMessage());
            return new Response($page->render());
        }
    }

    protected function add(RequestParameters $post): JsonResponse
    {
        $user = new User();
        $user->username = $post->getAlphaNum('username');
        $user->email = $post->getEmail('email') ?: null;
        $user->level = $post->getInt('level');
        $user->firstName = $post->getSimpleString('firstName');
        $user->tussenvoegsel = $post->getTussenvoegsel('tussenvoegsel');
        $user->lastName = $post->getSimpleString('lastName');
        $user->role = $post->getSimpleString('role');
        $user->comments = $post->getHTML('comments');
        $user->avatar = $post->getFilenameWithDirectory('avatar');
        $user->hideFromMemberList = $post->getBool('hideFromMemberList');

        $password = $post->getUnfilteredString('password') ?: Util::generatePassword();
        $user->setPassword($password);
        if (!$user->save())
        {
            throw new Exception('Could not add user!');
        }

        if ($user->email !== null)
        {
            $user->mailNewPassword($password);
        }

        return new JsonResponse(['userId' => $user->id]);
    }

    protected function edit(QueryBits $queryBits, RequestParameters $post): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }

        $user = User::fetchById($id);
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
        if (!$result)
        {
            return new JsonResponse(['error' => 'Could not update user!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse();
    }

    protected function delete(QueryBits $queryBits): JsonResponse
    {
        $userId = $queryBits->getInt(2);
        if ($userId < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User($userId);
        $user->delete();

        return new JsonResponse();
    }

    protected function resetPassword(QueryBits $queryBits): JsonResponse
    {
        $userId = $queryBits->getInt(2);
        $user = User::fetchById($userId);
        if ($user === null)
        {
            return new JsonResponse(['error' => 'User not found!', Response::HTTP_NOT_FOUND]);
        }

        $user->resetPassword();

        return new JsonResponse();
    }

    protected function changeAvatar(QueryBits $queryBits, Request $request): Response
    {
        $userId = $queryBits->getInt(2);
        $user = User::fetchById($userId);
        if ($user === null)
        {
            $page = new SimplePage('Fout bij veranderen avatar', 'Kon gebruiker niet vinden!');
            return new Response($page->render(), Response::HTTP_NOT_FOUND);
        }

        $user->uploadNewAvatar($request);

        return new RedirectResponse('/user/manager');
    }

    protected function changePasswordGet(): Response
    {
        return new Response((new ChangePasswordPage())->render());
    }

    protected function changePasswordPost(RequestParameters $post): Response
    {
        $profile = User::fromSession();
        if ($profile === null)
        {
            return new Response((new SimplePage('Fout', 'Geen profiel gevonden!'))->render(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $oldPassword = $post->getUnfilteredString('oldPassword');
        if (!$profile->checkPassword($oldPassword))
        {
            User::addNotification('Oude wachtwoord klopt niet!');
            return new RedirectResponse('/user/changePassword');
        }

        $newPassword = $post->getUnfilteredString('newPassword');
        if (strlen($newPassword) < 8)
        {
            User::addNotification('Nieuw wachtwoord moet langer zijn dan 8 tekens!');
            return new RedirectResponse('/user/changePassword');
        }

        $newPasswordRepeat = $post->getUnfilteredString('newPasswordRepeat');

        if ($newPassword !== $newPasswordRepeat)
        {
            User::addNotification('Wachtwoorden komen niet overeen!');
            return new RedirectResponse('/user/changePassword');
        }

        $changed = $profile->setPassword($newPassword) && $profile->save();
        if (!$changed)
        {
            return new Response((new SimplePage('Fout', 'Kon het nieuwe wachtwoord niet opslaan.'))->render(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        User::addNotification('Wachtwoord gewijzigd.');
        return new RedirectResponse('/');
    }
}
