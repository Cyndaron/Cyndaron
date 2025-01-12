<?php
declare(strict_types=1);

namespace Cyndaron\Util;

use BackedEnum;
use PDO;
use function file_exists;
use function assert;
use function is_array;
use function dirname;
use function file_put_contents;
use function var_export;

final class SettingsRepository
{
    public const CACHE_FILE = CACHE_DIR . 'settings.php';

    /** @var string[] */
    public static array $cache = [];

    public function __construct(private readonly PDO $connection)
    {
        if (file_exists(self::CACHE_FILE))
        {
            self::$cache = require self::CACHE_FILE;
        }
        else
        {
            $this->buildCache();
        }
    }

    public function buildCache(): void
    {
        $data = [];
        $settings = $this->connection->prepare('SELECT name,value FROM settings');
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

    public function get(string|BuiltinSetting|BackedEnum $name): string
    {
        if ($name instanceof BackedEnum)
        {
            $name = $name->value;
        }

        return self::$cache[$name] ?? '';
    }

    public function set(string|BuiltinSetting|BackedEnum $name, string $value): void
    {
        if ($name instanceof BackedEnum)
        {
            $name = $name->value;
        }

        $setting = $this->connection->prepare('REPLACE INTO settings(`name`, `value`) VALUES (?, ?)');
        $setting->execute([$name, $value]);
    }
}
