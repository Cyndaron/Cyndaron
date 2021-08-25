<?php
/** @noinspection PhpUnusedPrivateMethodInspection */
/** @noinspection SqlResolve */

namespace Cyndaron;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Request\QueryBits;
use Cyndaron\User\User;
use Cyndaron\View\SimplePage;
use Symfony\Component\HttpFoundation\Response;
use function array_key_exists;

final class MigrateController extends \Cyndaron\Routing\Controller
{
    public const VERSIONS = [
        '5.3' => 'migrate53',
        '6.0' => 'migrate60',
    ];

    protected function routeGet(QueryBits $queryBits): Response
    {
        $version = $this->action;

        if ($version !== null && array_key_exists($version, self::VERSIONS))
        {
            $method = self::VERSIONS[$version];
            if ($this->$method())
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

    private function migrate53(): bool
    {
        if (!User::isAdmin())
        {
            return false;
        }

        DBConnection::doQuery('ALTER TABLE `gebruikers` ADD `email` VARCHAR(255) NULL DEFAULT NULL AFTER `wachtwoord`;');
        DBConnection::doQuery('ALTER TABLE `gebruikers` ADD UNIQUE( `email`);');

        DBConnection::doQuery('ALTER TABLE `categorieen` ADD `categorieid` INT NULL AFTER `beschrijving`;');
        DBConnection::doQuery('ALTER TABLE `categorieen` ADD FOREIGN KEY (`categorieid`) REFERENCES `categorieen`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;');

        DBConnection::doQuery('ALTER TABLE `menu` ADD `isDropdown` BOOLEAN NOT NULL DEFAULT \'0\' AFTER `alias`, ADD `isImage` BOOLEAN NOT NULL DEFAULT \'0\' AFTER `isDropdown`;');
        // Bestaande menu-items porten
        DBConnection::doQuery('UPDATE `menu` SET link = REPLACE(link, \'#dd\', \'\'), isDropdown=1 WHERE link LIKE \'%#dd\'');
        DBConnection::doQuery('UPDATE `menu` SET alias = REPLACE(alias, \'img#\', \'\'), isImage=1 WHERE alias LIKE \'%img#\'');

        return true;
    }

    private function migrate60(): bool
    {
        return false;
    }
}
