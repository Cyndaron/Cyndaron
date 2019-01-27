<?php
namespace Cyndaron;

class Setting
{
    public static function get(string $name, bool $escape = false)
    {
        $connection = DBConnection::getPDO();
        $setting = $connection->prepare('SELECT waarde FROM instellingen WHERE naam= ?');
        $setting->execute([$name]);
        if (!$escape)
        {
            return $setting->fetchColumn();
        }

        return htmlspecialchars($setting->fetchColumn(), ENT_COMPAT | ENT_HTML5, 'UTF-8', false);
    }

    public static function set(string $name, string $value)
    {
        $connectie = DBConnection::getPDO();
        $setting = $connectie->prepare('REPLACE INTO instellingen(`naam`, `waarde`) VALUES (?, ?)');
        $setting->execute([$name, $value]);
    }
}