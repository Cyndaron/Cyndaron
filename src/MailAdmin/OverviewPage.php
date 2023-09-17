<?php
declare(strict_types=1);

namespace Cyndaron\MailAdmin;

use Cyndaron\Page\Page;
use Cyndaron\User\User;
use PDO;
use function assert;
use function usort;

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

        /** @var Domain[] $addressesPerDomain */
        $addressesPerDomain = [];
        foreach ($domains as $domain)
        {
            $id = (int)$domain['id'];
            $addressesPerDomain[$id] = new Domain($id, $domain['name']);
        }
        foreach ($users as $user)
        {
            $id = (int)$user['id'];
            $domainId = (int)$user['domain_id'];

            $addressesPerDomain[$domainId]->addresses[] = new UserEntry($id, $domainId, $user['email']);
        }
        foreach ($aliases as $alias)
        {
            $id = (int)$alias['id'];
            $domainId = (int)$alias['domain_id'];

            $addressesPerDomain[$domainId]->addresses[] = new AliasEntry($id, $domainId, $alias['source'], $alias['destination']);
        }

        usort($addressesPerDomain, static function(Domain $domain1, Domain $domain2)
        {
            return $domain1->name <=> $domain2->name;
        });
        foreach ($addressesPerDomain as $domain)
        {
            usort($domain->addresses, static function(EmailEntry $entry1, EmailEntry $entry2)
            {
                return $entry1->getEmail() <=> $entry2->getEmail();
            });
        }

        $this->addTemplateVars([
            'domains' => $domains,
            'users' => $users,
            'aliases' => $aliases,
            'addressesPerDomain' => $addressesPerDomain,
            'csrfTokenAddAlias' => User::getCSRFToken('mailadmin', 'addAlias'),
            'csrfTokenAddDomain' => User::getCSRFToken('mailadmin', 'addDomain'),
            'csrfTokenAddEmail' => User::getCSRFToken('mailadmin', 'addEmail'),
            'csrfTokenDeleteAlias' => User::getCSRFToken('mailadmin', 'deleteAlias'),
            'csrfTokenDeleteEmail' => User::getCSRFToken('mailadmin', 'deleteEmail'),
        ]);
    }
}
