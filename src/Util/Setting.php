<?php
namespace Cyndaron\Util;

use BackedEnum;
use PDO;
use function file_exists;
use function file_put_contents;
use function is_array;
use function var_export;
use const ROOT_DIR;
use function dirname;
use function assert;

final class Setting
{
    public const VALUE_ORGANISATION_VOV = 'Vlissingse Oratorium Vereniging';
    public const VALUE_ORGANISATION_ZCK = 'Zeeuws Concertkoor';
    public const VALUE_ORGANISATION_TFR = 'The Flood Requiem 1953';

    public const CACHE_FILE = ROOT_DIR . '/cache/cyndaron/settings.php';

    /** @var string[] */
    public static array $cache = [];

    private static PDO $pdo;

    public static function get(string|BuiltinSetting|BackedEnum $name): string
    {
        if ($name instanceof BackedEnum)
        {
            $name = $name->value;
        }

        return self::$cache[$name] ?? '';
    }

    public static function set(string|BuiltinSetting|BackedEnum $name, string $value): void
    {
        if ($name instanceof BackedEnum)
        {
            $name = $name->value;
        }

        $setting = self::$pdo->prepare('REPLACE INTO settings(`name`, `value`) VALUES (?, ?)');
        $setting->execute([$name, $value]);
    }

    public static function getShortCode(): string
    {
        $value = self::get(BuiltinSetting::SHORT_CODE);
        if ($value !== '')
        {
            return $value;
        }

        switch (self::get(BuiltinSetting::ORGANISATION))
        {
            case self::VALUE_ORGANISATION_VOV:
            case self::VALUE_ORGANISATION_ZCK:
                return 'vov';
            case self::VALUE_ORGANISATION_TFR:
                return 'tfr';
        }

        return '';
    }

    public static function load(PDO $pdo): void
    {
        self::$pdo = $pdo;
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
        $settings = self::$pdo->prepare('SELECT name,value FROM settings');
        $settings->execute([]);
        while ($row = $settings->fetch())
        {
            assert(is_array($row));
            $data[$row['name']] = $row['value'];
        }

        self::$cache = $data;
        Util::ensureDirectoryExists(dirname(self::CACHE_FILE));
        file_put_contents(self::CACHE_FILE, "<?php\nreturn " . var_export($data, true) . ";");
    }
}
