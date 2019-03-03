<?php
namespace Cyndaron;

require __DIR__ . '/../check.php';

class Migrate53 extends Pagina
{
    public function __construct()
    {
        DBConnection::doQuery('ALTER TABLE `gebruikers` ADD `email` VARCHAR(255) NULL DEFAULT NULL AFTER `wachtwoord`;');
        DBConnection::doQuery('ALTER TABLE `gebruikers` ADD UNIQUE( `email`);');

        DBConnection::doQuery('ALTER TABLE `categorieen` ADD `categorieid` INT NULL AFTER `beschrijving`;', []);
        DBConnection::doQuery('ALTER TABLE `categorieen` ADD FOREIGN KEY (`categorieid`) REFERENCES `categorieen`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;', []);

        DBConnection::doQuery('ALTER TABLE `menu` ADD `isDropdown` BOOLEAN NOT NULL DEFAULT \'0\' AFTER `alias`, ADD `isImage` BOOLEAN NOT NULL DEFAULT \'0\' AFTER `isDropdown`;', []);
        // Bestaande menu-items porten
        DBConnection::doQuery('UPDATE `menu` SET link = REPLACE(link, \'#dd\', \'\'), isDropdown=1 WHERE link LIKE \'%#dd\'', []);
        DBConnection::doQuery('UPDATE `menu` SET alias = REPLACE(alias, \'img#\', \'\'), isImage=1 WHERE alias LIKE \'%img#\'', []);


        parent::__construct('Upgrade naar versie 5.3');
        $this->showPrePage();
        echo 'De upgrade is voltooid.';
        $this->showPostPage();
    }
}
