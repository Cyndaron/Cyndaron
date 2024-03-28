<?php
declare(strict_types=1);

namespace Cyndaron\MailAdmin;

use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\Setting;
use PDO;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use function crypt;
use function mb_substr;
use function rtrim;
use function base64_encode;
use function random_bytes;
use function str_contains;

class Controller extends \Cyndaron\Routing\Controller
{
    public array $getRoutes = [
        'overview' => ['function' => 'overview', 'level' => UserLevel::ADMIN],
    ];
    public array $postRoutes = [
        'addAlias' => ['function' => 'addAlias', 'level' => UserLevel::ADMIN],
        'addDomain' => ['function' => 'addDomain', 'level' => UserLevel::ADMIN],
        'addEmail' => ['function' => 'addEmail', 'level' => UserLevel::ADMIN],
        'deleteAlias' => ['function' => 'deleteAlias', 'level' => UserLevel::ADMIN],
        'deleteEmail' => ['function' => 'deleteEmail', 'level' => UserLevel::ADMIN],
    ];

    public function createPDO(): PDO
    {
        $username = Setting::get('postfix_sql_username');
        $password = Setting::get('postfix_sql_password');
        $database = Setting::get('postfix_sql_database');

        $pdo = new PDO("mysql://host=localhost;dbname={$database}", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public function overview(): Response
    {
        $page = new OverviewPage($this->createPDO());
        return $this->pageRenderer->renderResponse($page);
    }

    public function addDomain(RequestParameters $post): RedirectResponse
    {
        $domain = $post->getSimpleString('domain');
        $pdo = $this->createPDO();

        $prep = $pdo->prepare('INSERT INTO virtual_domains(`name`) VALUES (?)');
        $prep->execute([$domain]);

        return new RedirectResponse('/mailadmin/overview');
    }

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

    public function deleteAlias(QueryBits $queryBits): RedirectResponse
    {
        $id = $queryBits->getInt(2);

        $pdo = $this->createPDO();
        $prep = $pdo->prepare("DELETE FROM virtual_aliases WHERE id = ?");
        $prep->execute([$id]);

        return new RedirectResponse('/mailadmin/overview');
    }

    public function deleteEmail(QueryBits $queryBits): RedirectResponse
    {
        $id = $queryBits->getInt(2);

        $pdo = $this->createPDO();
        $prep = $pdo->prepare("DELETE FROM virtual_users WHERE id = ?");
        $prep->execute([$id]);

        return new RedirectResponse('/mailadmin/overview');
    }
}
