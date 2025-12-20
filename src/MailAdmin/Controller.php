<?php
declare(strict_types=1);

namespace Cyndaron\MailAdmin;

use Cyndaron\Page\PageRenderer;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\SettingsRepository;
use PDO;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use function crypt;
use function mb_substr;
use function rtrim;
use function base64_encode;
use function random_bytes;
use function str_contains;

final class Controller
{
    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly SettingsRepository $settingsRepository,
    ) {
    }

    private function createPDO(): PDO
    {
        $username = $this->settingsRepository->get('postfix_sql_username');
        $password = $this->settingsRepository->get('postfix_sql_password');
        $database = $this->settingsRepository->get('postfix_sql_database');

        $pdo = new PDO("mysql://host=localhost;dbname={$database}", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    #[RouteAttribute('overview', RequestMethod::GET, UserLevel::ADMIN)]
    public function overview(CSRFTokenHandler $tokenHandler): Response
    {
        $page = new OverviewPage($this->createPDO(), $tokenHandler);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('addDomain', RequestMethod::POST, UserLevel::ADMIN)]
    public function addDomain(RequestParameters $post): RedirectResponse
    {
        $domain = $post->getSimpleString('domain');
        $pdo = $this->createPDO();

        $prep = $pdo->prepare('INSERT INTO virtual_domains(`name`) VALUES (?)');
        $prep->execute([$domain]);

        return new RedirectResponse('/mailadmin/overview');
    }

    #[RouteAttribute('addAlias', RequestMethod::POST, UserLevel::ADMIN)]
    public function addAlias(RequestParameters $post): RedirectResponse
    {
        $pdo = $this->createPDO();

        $user = $post->getSimpleString('user');
        $destination = $post->getEmail('destination');
        $domainId = $post->getInt('domainId');
        $prep = $pdo->prepare('SELECT name FROM virtual_domains WHERE id = ?');
        $prep->execute([$domainId]);
        $domain = $prep->fetchColumn();

        $source = "{$user}@{$domain}";
        if (!str_contains($destination, '@'))
        {
            $destination .= "@{$domain}";
        }

        $prep = $pdo->prepare("INSERT INTO virtual_aliases(`domain_id`, `source`, `destination`) VALUES (?, ?, ?)");
        $prep->execute([$domainId, $source, $destination]);

        return new RedirectResponse('/mailadmin/overview');
    }

    #[RouteAttribute('addEmail', RequestMethod::POST, UserLevel::ADMIN)]
    public function addEmail(RequestParameters $post): RedirectResponse
    {
        $pdo = $this->createPDO();

        $user = $post->getSimpleString('user');
        $domainId = $post->getInt('domainId');
        $password = $post->getUnfilteredString('password');
        $hashedPassword = $this->hashPassword($password);
        $prep = $pdo->prepare('SELECT name FROM virtual_domains WHERE id = ?');
        $prep->execute([$domainId]);
        $domain = $prep->fetchColumn();

        $emailAddress = "{$user}@{$domain}";

        $prep = $pdo->prepare("INSERT INTO virtual_users(`domain_id`, `password`, `email`) VALUES (?, ?, ?)");
        $prep->execute([$domainId, $hashedPassword, $emailAddress]);

        return new RedirectResponse('/mailadmin/overview');
    }

    private function hashPassword(string $input): string
    {
        $salt = mb_substr(rtrim(base64_encode(random_bytes(16)), '='), 0, 16, '8bit');
        return crypt($input, '$6$' . $salt);
    }

    #[RouteAttribute('deleteAlias', RequestMethod::POST, UserLevel::ADMIN)]
    public function deleteAlias(QueryBits $queryBits): RedirectResponse
    {
        $id = $queryBits->getInt(2);

        $pdo = $this->createPDO();
        $prep = $pdo->prepare("DELETE FROM virtual_aliases WHERE id = ?");
        $prep->execute([$id]);

        return new RedirectResponse('/mailadmin/overview');
    }

    #[RouteAttribute('deleteEmail', RequestMethod::POST, UserLevel::ADMIN)]
    public function deleteEmail(QueryBits $queryBits): RedirectResponse
    {
        $id = $queryBits->getInt(2);

        $pdo = $this->createPDO();
        $prep = $pdo->prepare("DELETE FROM virtual_users WHERE id = ?");
        $prep->execute([$id]);

        return new RedirectResponse('/mailadmin/overview');
    }
}
