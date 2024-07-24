<?php
declare(strict_types=1);

namespace Cyndaron\User;

use Cyndaron\Geelhoed\Contest\Contest;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Request\UrlInfo;
use Cyndaron\Routing\Controller;
use Cyndaron\Routing\Kernel;
use Cyndaron\Routing\RouteAttribute;
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
    #[RouteAttribute('gallery', RequestMethod::GET, UserLevel::LOGGED_IN)]
    public function gallery(User|null $currentUser): Response
    {
        // Has to be done here because you cannot specify the expression during member variable initialization.
        $minLevel = (int)Setting::get('userGalleryMinLevel') ?: UserLevel::ADMIN;
        $userLevel = $currentUser !== null ? $currentUser->level : UserLevel::ANONYMOUS;
        if ($userLevel < $minLevel)
        {
            $page = new SimplePage('Fout', 'U heeft onvoldoende rechten om deze pagina te bekijken.');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_UNAUTHORIZED);
        }

        $page = new Gallery();
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('login', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function loginGet(Request $request, CSRFTokenHandler $tokenHandler, UserSession $userSession): Response
    {
        if (empty($userSession->getRedirect()))
        {
            $userSession->setRedirect($request->headers->get('referer'));
        }
        $page = new LoginPage($tokenHandler);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('logout', RequestMethod::GET, UserLevel::LOGGED_IN)]
    public function logout(UserSession $userSession): Response
    {
        $userSession->logout();
        return new RedirectResponse('/', Response::HTTP_FOUND, Kernel::HEADERS_DO_NOT_CACHE);
    }

    #[RouteAttribute('manager', RequestMethod::GET, UserLevel::ADMIN)]
    public function manager(): Response
    {
        $page = new UserManagerPage();
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('login', RequestMethod::POST, UserLevel::ANONYMOUS)]
    public function loginPost(RequestParameters $post, UserSession $userSession): Response
    {
        $identification = $post->getEmail('login_user');
        $verification = $post->getUnfilteredString('login_pass');

        try
        {
            $redirectUrl = User::login($identification, $verification, $userSession);
            return new RedirectResponse($redirectUrl);
        }
        catch (IncorrectCredentials $e)
        {
            $page = new SimplePage('Inloggen mislukt', $e->getMessage());
            return $this->pageRenderer->renderResponse($page);
        }
        catch (Exception $e)
        {
            $page = new SimplePage('Inloggen mislukt', 'Onbekende fout: ' . $e->getMessage());
            return $this->pageRenderer->renderResponse($page);
        }
    }

    #[RouteAttribute('add', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function add(RequestParameters $post, UrlInfo $urlInfo): JsonResponse
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
            $user->mailNewPassword($urlInfo->domain, $password);
        }

        return new JsonResponse(['userId' => $user->id]);
    }

    #[RouteAttribute('edit', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function edit(QueryBits $queryBits, RequestParameters $post): JsonResponse
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

    #[RouteAttribute('delete', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function delete(QueryBits $queryBits): JsonResponse
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

    #[RouteAttribute('resetpassword', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: Contest::RIGHT_MANAGE)]
    public function resetPassword(QueryBits $queryBits, UrlInfo $urlInfo): JsonResponse
    {
        $userId = $queryBits->getInt(2);
        $user = User::fetchById($userId);
        if ($user === null)
        {
            return new JsonResponse(['error' => 'User not found!', Response::HTTP_NOT_FOUND]);
        }

        $newPassword = $user->generatePassword();
        $user->save();
        $user->mailNewPassword($urlInfo->domain, $newPassword);

        return new JsonResponse();
    }

    #[RouteAttribute('changeAvatar', RequestMethod::POST, UserLevel::ADMIN)]
    public function changeAvatar(QueryBits $queryBits, Request $request): Response
    {
        $userId = $queryBits->getInt(2);
        $user = User::fetchById($userId);
        if ($user === null)
        {
            $page = new SimplePage('Fout bij veranderen avatar', 'Kon gebruiker niet vinden!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_NOT_FOUND);
        }

        $user->uploadNewAvatar($request);

        return new RedirectResponse('/user/manager');
    }

    #[RouteAttribute('changePassword', RequestMethod::GET, UserLevel::LOGGED_IN)]
    public function changePasswordGet(CSRFTokenHandler $tokenHandler): Response
    {
        return $this->pageRenderer->renderResponse(new ChangePasswordPage($tokenHandler));
    }

    #[RouteAttribute('changePassword', RequestMethod::POST, UserLevel::LOGGED_IN)]
    public function changePasswordPost(RequestParameters $post, UserSession $userSession): Response
    {
        $profile = User::fromSession($userSession);
        if ($profile === null)
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Fout', 'Geen profiel gevonden!'), status:  Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $oldPassword = $post->getUnfilteredString('oldPassword');
        if (!$profile->checkPassword($oldPassword))
        {
            $userSession->addNotification('Oude wachtwoord klopt niet!');
            return new RedirectResponse('/user/changePassword');
        }

        $newPassword = $post->getUnfilteredString('newPassword');
        if (strlen($newPassword) < 8)
        {
            $userSession->addNotification('Nieuw wachtwoord moet langer zijn dan 8 tekens!');
            return new RedirectResponse('/user/changePassword');
        }

        $newPasswordRepeat = $post->getUnfilteredString('newPasswordRepeat');

        if ($newPassword !== $newPasswordRepeat)
        {
            $userSession->addNotification('Wachtwoorden komen niet overeen!');
            return new RedirectResponse('/user/changePassword');
        }

        $changed = $profile->setPassword($newPassword) && $profile->save();
        if (!$changed)
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Fout', 'Kon het nieuwe wachtwoord niet opslaan.'), status:  Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $userSession->addNotification('Wachtwoord gewijzigd.');
        return new RedirectResponse('/');
    }
}
