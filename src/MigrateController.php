<?php
/** @noinspection PhpUnusedPrivateMethodInspection */
/** @noinspection SqlResolve */

namespace Cyndaron;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\DBAL\Connection;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\User\User;
use Symfony\Component\HttpFoundation\Response;
use function array_key_exists;

final class MigrateController extends \Cyndaron\Routing\Controller
{
    public const VERSIONS = [
        '5.3' => 'migrate53',
        '6.0' => 'migrate60',
    ];

    protected function routeGet(QueryBits $queryBits, Connection $db): Response
    {
        $version = $this->action;

        if ($version !== null && array_key_exists($version, self::VERSIONS))
        {
            $method = self::VERSIONS[$version];
            if ($this->$method($db))
            {
                $page = new SimplePage('Upgrade naar versie ' . $version, 'De upgrade is voltooid.');
                return new Response($page->render());
            }

            $page = new SimplePage('Upgrade mislukt', 'Onbekende oorzaak');
            return new Response($page->render(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $page = new SimplePage('Upgrade mislukt', 'Onbekende versie');
        return new Response($page->render(), Response::HTTP_NOT_FOUND);
    }

    private function migrate53(Connection $db): bool
    {
        if (!User::isAdmin())
        {
            return false;
        }

        $db->executeQuery('ALTER TABLE `gebruikers` ADD `email` VARCHAR(255) NULL DEFAULT NULL AFTER `wachtwoord`;');
        $db->executeQuery('ALTER TABLE `gebruikers` ADD UNIQUE( `email`);');

        $db->executeQuery('ALTER TABLE `categorieen` ADD `categorieid` INT NULL AFTER `beschrijving`;');
        $db->executeQuery('ALTER TABLE `categorieen` ADD FOREIGN KEY (`categorieid`) REFERENCES `categorieen`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;');

        $db->executeQuery('ALTER TABLE `menu` ADD `isDropdown` BOOLEAN NOT NULL DEFAULT \'0\' AFTER `alias`, ADD `isImage` BOOLEAN NOT NULL DEFAULT \'0\' AFTER `isDropdown`;');
        // Bestaande menu-items porten
        $db->executeQuery('UPDATE `menu` SET link = REPLACE(link, \'#dd\', \'\'), isDropdown=1 WHERE link LIKE \'%#dd\'');
        $db->executeQuery('UPDATE `menu` SET alias = REPLACE(alias, \'img#\', \'\'), isImage=1 WHERE alias LIKE \'%img#\'');

        return true;
    }

    private function migrate60(): bool
    {
        return false;
    }
}
