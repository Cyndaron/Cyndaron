<?php
declare(strict_types=1);

namespace Cyndaron\MailAdmin;

use Cyndaron\User\User;
use Cyndaron\View\Page;
use PDO;
use function assert;

class OverviewPage extends Page
{
    public function __construct(PDO $pdo)
    {
        parent::__construct('Mailadmin');

        $stmt = $pdo->query('SELECT * FROM virtual_domains');
        assert($stmt !== false);
        $domains = $stmt->fetchAll();

        $stmt = $pdo->query('SELECT * FROM virtual_users');
        assert($stmt !== false);
        $users = $stmt->fetchAll();

        $stmt = $pdo->query('SELECT * FROM virtual_aliases');
        assert($stmt !== false);
        $aliases = $stmt->fetchAll();

        $this->addTemplateVars([
            'domains' => $domains,
            'users' => $users,
            'aliases' => $aliases,
            'csrfTokenAddDomain' => User::getCSRFToken('mailadmin', 'addDomain'),
            'csrfTokenAddEmail' => User::getCSRFToken('mailadmin', 'addEmail'),
        ]);
    }
}
