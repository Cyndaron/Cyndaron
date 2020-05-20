<?php
namespace Cyndaron;

class Setting
{
    public const ORGANISATION_VOV = 'Vlissingse Oratorium Vereniging';
    public const ORGANISATION_SBK = 'Stichting Bijzondere Koorprojecten';

    public static function get(string $name)
    {
        $connection = DBConnection::getPDO();
        $setting = $connection->prepare('SELECT value FROM settings WHERE name= ?');
        $setting->execute([$name]);

        return $setting->fetchColumn();
    }

    public static function set(string $name, string $value): void
    {
        $connection = DBConnection::getPDO();
        $setting = $connection->prepare('REPLACE INTO settings(`name`, `value`) VALUES (?, ?)');
        $setting->execute([$name, $value]);
    }
}
