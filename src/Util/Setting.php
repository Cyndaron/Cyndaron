<?php
namespace Cyndaron\Util;

use Cyndaron\DBAL\DBConnection;
use function file_exists;
use function file_put_contents;
use function var_export;
use const ROOT_DIR;
use function dirname;

final class Setting
{
    public const ORGANISATION = 'organisation';

    public const VALUE_ORGANISATION_VOV = 'Vlissingse Oratorium Vereniging';
    public const VALUE_ORGANISATION_ZCK = 'Zeeuws Concertkoor';
    public const VALUE_ORGANISATION_SBK = 'Stichting Bijzondere Koorprojecten';
    public const VALUE_ORGANISATION_TFR = 'The Flood Requiem 1953';

    public const CACHE_FILE = ROOT_DIR . '/cache/cyndaron/settings.php';

    /** @var string[] */
    public static array $cache = [];

    /**
     * @param string $name
     * @return string
     */
    public static function get(string $name): string
    {
        return self::$cache[$name] ?? '';
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

    public static function load(): void
    {
        if (file_exists(self::CACHE_FILE))
        {
            self::$cache = require self::CACHE_FILE;
        }
        else
        {
            self::buildCache();
        }
    }

    public static function buildCache(): void
    {
        $data = [];
        $connection = DBConnection::getPDO();
        $settings = $connection->prepare('SELECT name,value FROM settings');
        $settings->execute([]);
        while ($row = $settings->fetch())
        {
            $data[$row['name']] = $row['value'];
        }

        self::$cache = $data;
        Util::ensureDirectoryExists(dirname(self::CACHE_FILE));
        file_put_contents(self::CACHE_FILE, "<?php\nreturn " . var_export($data, true) . ";");
    }
}
