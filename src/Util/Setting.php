<?php
namespace Cyndaron\Util;

use Cyndaron\DBAL\DBConnection;

final class Setting
{
    public const ORGANISATION = 'organisation';

    public const VALUE_ORGANISATION_VOV = 'Vlissingse Oratorium Vereniging';
    public const VALUE_ORGANISATION_ZCK = 'Zeeuws Concertkoor';
    public const VALUE_ORGANISATION_SBK = 'Stichting Bijzondere Koorprojecten';
    public const VALUE_ORGANISATION_TFR = 'The Flood Requiem 1953';

    /**
     * @param string $name
     * @return string
     */
    public static function get(string $name): string
    {
        $connection = DBConnection::getPDO();
        $setting = $connection->prepare('SELECT value FROM settings WHERE name= ?');
        $setting->execute([$name]);

        return (string)$setting->fetchColumn();
    }

    public static function set(string $name, string $value): void
    {
        $connection = DBConnection::getPDO();
        $setting = $connection->prepare('REPLACE INTO settings(`name`, `value`) VALUES (?, ?)');
        $setting->execute([$name, $value]);
    }

    public static function getShortCode(): string
    {
        switch (self::get(self::ORGANISATION))
        {
            case self::VALUE_ORGANISATION_VOV:
            case self::VALUE_ORGANISATION_ZCK:
                return 'vov';
            case self::VALUE_ORGANISATION_TFR:
                return 'tfr';
        }

        return '';
    }
}
