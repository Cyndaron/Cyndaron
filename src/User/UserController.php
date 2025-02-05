<?php
declare(strict_types=1);

namespace Cyndaron\User;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\Geelhoed\Contest\Model\Contest;
use Cyndaron\Imaging\GdHelper;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\Kernel;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Translation\Translator;
use Cyndaron\Util\MailFactory;
use Cyndaron\Util\Setting;
use Cyndaron\Util\Util;
use Exception;
use Safe\Exceptions\ImageException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use function basename;
use function file_exists;
use function Safe\imagepng;
use function Safe\unlink;
use function sprintf;
use function str_contains;
use function strlen;

final class UserController
{
    private const RESET_PASSWORD_MAIL_TEXT =
        'U vroeg om een nieuw wachtwoord voor %s.

Uw nieuwe wachtwoord is: %s';

    public function __construct(private readonly PageRenderer $pageRenderer)
    {

    }

    #[RouteAttribute('gallery', RequestMethod::GET, UserLevel::LOGGED_IN)]
    public function gallery(UserSession $session, Translator $t): Response
    {
        $currentUser = $session->getProfile();
        // Has to be done here because you cannot specify the expression during member variable initialization.
        $minLevel = (int)Setting::get('userGalleryMinLevel') ?: UserLevel::ADMIN;
        $userLevel = $currentUser !== null ? $currentUser->level : UserLevel::ANONYMOUS;
        if ($userLevel < $minLevel)
        {
            $page = new SimplePage($t->get('Fout'), $t->get('U heeft onvoldoende rechten om deze pagina te bekijken.'));
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_UNAUTHORIZED);
        }

        $page = new Gallery();
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('login', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function loginGet(Request $request, CSRFTokenHandler $tokenHandler, UserSession $userSession, Translator $translator): Response
    {
        if (empty($userSession->getRedirect()))
        {
            $userSession->setRedirect($request->headers->get('referer'));
        }
        $page = new LoginPage($tokenHandler, $translator);
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
    public function loginPost(RequestParameters $post, UserSession $userSession, Translator $t, UserRepository $repository): Response
    {
        $identification = $post->getEmail('login_user');
        $password = $post->getUnfilteredString('login_pass');

        try
        {
            if (str_contains($identification, '@'))
            {
                $user = $repository->fetchByEmail($identification);
            }
            else
            {
                $user = $repository->fetchByUsername($identification);
            }

            if ($user === null)
            {
                throw new IncorrectCredentials('Onbekende gebruikersnaam of e-mailadres.');
            }

            if (!$user->passwordIsCorrect($password))
            {
                throw new IncorrectCredentials('Verkeerd wachtwoord.');
            }

            if ($user->passwordNeedsUpdate())
            {
                $user->setPassword($password);
                $user->save();
            }

            $userSession->setProfile($user);
            $userSession->addNotification($t->get('U bent ingelogd.'));

            $sessionRedirect = $userSession->getRedirect();
            if ($sessionRedirect !== '')
            {
                $redirectUrl = $sessionRedirect;
                $userSession->setRedirect(null);
            }
            else
            {
                $redirectUrl = '/';
            }

            return new RedirectResponse($redirectUrl);
        }
        catch (IncorrectCredentials $e)
        {
            $page = new SimplePage($t->get('Inloggen mislukt'), $e->getMessage());
            return $this->pageRenderer->renderResponse($page);
        }
        catch (Exception $e)
        {
            $page = new SimplePage($t->get('Inloggen mislukt'), $t->get('Onbekende fout: ') . $e->getMessage());
            return $this->pageRenderer->renderResponse($page);
        }
    }

    #[RouteAttribute('add', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function add(RequestParameters $post, MailFactory $mailFactory): JsonResponse
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
            $this->mailNewPassword($user, $password, $mailFactory);
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
    public function delete(QueryBits $queryBits, GenericRepository $repository): JsonResponse
    {
        $userId = $queryBits->getInt(2);
        if ($userId < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }

        $repository->deleteById(User::class, $userId);

        return new JsonResponse();
    }

    #[RouteAttribute('resetpassword', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: Contest::RIGHT_MANAGE)]
    public function resetPassword(QueryBits $queryBits, MailFactory $mailFactory): JsonResponse
    {
        $userId = $queryBits->getInt(2);
        $user = User::fetchById($userId);
        if ($user === null)
        {
            return new JsonResponse(['error' => 'User not found!', Response::HTTP_NOT_FOUND]);
        }

        $newPassword = Util::generatePassword();
        $user->setPassword($newPassword);
        $user->save();
        $this->mailNewPassword($user, $newPassword, $mailFactory);

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

        Util::ensureDirectoryExists(User::AVATAR_DIR);
        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $file = $request->files->get('avatarFile');
        $tmpName = $file->getPathname();

        try
        {
            $avatarImg = GdHelper::fromFilename($tmpName);
        }
        catch (ImageException)
        {
            throw new Exception('Kon de bestandsinhoud niet verwerken!');
        }

        $filename = User::AVATAR_DIR . "/{$user->id}.png";
        if (file_exists($filename))
        {
            unlink($filename);
        }

        imagepng($avatarImg, $filename);
        unlink($tmpName);

        $user->avatar = basename($filename);
        $user->save();

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
        $profile = $userSession->getProfile();
        if ($profile === null)
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Fout', 'Geen profiel gevonden!'), status:  Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $oldPassword = $post->getUnfilteredString('oldPassword');
        if (!$profile->passwordIsCorrect($oldPassword))
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

    private function mailNewPassword(User $user, string $password, MailFactory $mailFactory): bool
    {
        if ($user->email === null)
        {
            throw new Exception('No email address specified!');
        }

        $mail = $mailFactory->createMailWithDefaults(
            new Address($user->email),
            'Nieuw wachtwoord ingesteld',
            sprintf(self::RESET_PASSWORD_MAIL_TEXT, Setting::get('siteName'), $password)
        );
        return $mail->send();
    }
}
