<?php
/** @noinspection PhpUnusedPrivateMethodInspection */
/** @noinspection SqlResolve */

namespace Cyndaron;

use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

class MigrateController extends Controller
{
    protected int $minLevelGet = UserLevel::ANONYMOUS;

    public const VERSIONS = [
        '5.3' => 'migrate53',
        '6.0' => 'migrate60',
    ];

    protected function routeGet(): Response
    {
        $version = $this->action;

        if (array_key_exists($version, static::VERSIONS))
        {
            $method = static::VERSIONS[$version];
            $this->$method();

            $page = new Page('Upgrade naar versie ' . $version, 'De upgrade is voltooid.');
            return new Response($page->render());
        }

        $page = new Page('Upgrade mislukt', 'Onbekende versie');
        return new Response($page->render(), Response::HTTP_NOT_FOUND);
    }

    private function migrate53(): void
    {
        if (!User::isAdmin())
        {
            die();
        }

        DBConnection::doQuery('ALTER TABLE `gebruikers` ADD `email` VARCHAR(255) NULL DEFAULT NULL AFTER `wachtwoord`;');
        DBConnection::doQuery('ALTER TABLE `gebruikers` ADD UNIQUE( `email`);');

        DBConnection::doQuery('ALTER TABLE `categorieen` ADD `categorieid` INT NULL AFTER `beschrijving`;');
        DBConnection::doQuery('ALTER TABLE `categorieen` ADD FOREIGN KEY (`categorieid`) REFERENCES `categorieen`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;');

        DBConnection::doQuery('ALTER TABLE `menu` ADD `isDropdown` BOOLEAN NOT NULL DEFAULT \'0\' AFTER `alias`, ADD `isImage` BOOLEAN NOT NULL DEFAULT \'0\' AFTER `isDropdown`;');
        // Bestaande menu-items porten
        DBConnection::doQuery('UPDATE `menu` SET link = REPLACE(link, \'#dd\', \'\'), isDropdown=1 WHERE link LIKE \'%#dd\'');
        DBConnection::doQuery('UPDATE `menu` SET alias = REPLACE(alias, \'img#\', \'\'), isImage=1 WHERE alias LIKE \'%img#\'');
    }

    private function migrate60(): void
    {
        return;

//        DBConnection::doQuery("RENAME TABLE gebruikers TO users;");
//        DBConnection::doQuery("ALTER TABLE `users` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
//        DBConnection::doQuery("ALTER TABLE `users` CHANGE `gebruikersnaam` `username` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
//        DBConnection::doQuery("ALTER TABLE `users` CHANGE `wachtwoord` `password` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
//        DBConnection::doQuery("ALTER TABLE `users` CHANGE `niveau` `level` INT(1) NOT NULL;");

//        if (!User::isAdmin())
//        {
//            die();
//        }

//        $sql = file_get_contents(__DIR__ . '/Migrate60.sql');
//        DBConnection::doQuery($sql);

//        Setting::set('ticketsale_reservedSeatsDescription',
//            'Alle plaatsen in het middenschip van de kerk verkopen wij met een stoelnummer; d.w.z. al deze plaatsen worden
//            verkocht als gereserveerde plaats. De stoelnummers lopen van 1 t/m circa %d. Het is een doorlopende reeks,
//            dus dit keer geen rijnummer. Aan het einde van een rij verspringt het stoelnummer naar de stoel daarachter.
//            De nummers vormen een soort heen en weer gaande slinger door het hele middenschip heen. Het kan dus gebeuren
//            dat u een paar kaarten koopt, waarbij de nummering verspringt naar de rij daarachter. Maar wel zo dat de
//            stoelen dus direct bij elkaar staan.
//            Vrije plaatsen zijn: de zijvakken en de balkons.');

//        $frontPage = DBConnection::doQueryAndFetchOne('SELECT link FROM menu WHERE id=(SELECT MIN(id) FROM menu)');
//        Setting::set('frontPage', $frontPage);
    }
}

