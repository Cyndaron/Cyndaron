<?php
/**
 * Copyright © 2009-2025 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
namespace Cyndaron\Util;

use Cyndaron\CyndaronInfo;
use RuntimeException;
use Safe\DateTimeImmutable;
use Safe\Exceptions\FilesystemException;
use function Safe\preg_replace;
use function Safe\date;
use function Safe\mkdir;
use function sprintf;
use function substr;
use function Safe\unlink;
use function random_int;
use function count;
use function bin2hex;
use function random_bytes;
use function strtr;
use function strtolower;
use function file_exists;
use function umask;
use function floor;
use function strpos;
use function dirname;
use function strlen;
use function is_dir;
use function str_replace;
use function number_format;
use function stream_context_create;
use function Safe\file_get_contents;

final class Util
{
    public const UPLOAD_DIR = PUB_DIR . '/uploads';

    private const PASSWORD_CHARACTERS = ['a', 'c', 'd', 'e', 'f', 'h', 'j', 'm', 'n', 'q', 'r', 't',
        'A', 'C', 'D', 'E', 'F', 'H', 'J', 'L', 'M', 'N', 'Q', 'R', 'T',
        '3', '4', '7', '8'];

    public const SQL_DATE_FORMAT = 'Y-m-d';
    public const SQL_DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    public const BYTE_POSTFIXES = [
        ' B',
        ' KiB',
        ' MiB',
        ' GiB',
        ' TiB',
        ' PiB',
        ' EiB',
        ' ZiB',
        ' YiB'
    ];

    public static function generatePassword(int $length = 10): string
    {
        $gencode = '';

        for ($c = 0; $c < $length; $c++)
        {
            $gencode .= self::PASSWORD_CHARACTERS[random_int(0, count(self::PASSWORD_CHARACTERS) - 1)];
        }

        return $gencode;
    }

    public static function generateToken(int $length): string
    {
        // @phpstan-ignore-next-line Bogus error.
        return bin2hex(random_bytes($length));
    }

    public static function slug(string $string): string
    {
        return strtr(strtolower($string), [
            ' ' => '-'
        ]);
    }

    public static function createDir(string $dir, int $mask = 0777): bool
    {
        if (file_exists($dir))
        {
            if (is_dir($dir))
            {
                return true;
            }
            throw new FilesystemException('A file with this name exists!');
        }

        try
        {
            $oldUmask = umask(0);
            mkdir($dir, $mask, true);
            umask($oldUmask);
        }
        catch (FilesystemException)
        {
            return false;
        }

        return true;
    }

    public static function getStartOfNextQuarter(): DateTimeImmutable
    {
        $year = (int)date('Y');
        $nextYear = $year + 1;
        $currentQuarter = (int)floor(((int)date('m') - 1) / 3) + 1;

        switch ($currentQuarter)
        {
            case 1:
                $date = "$year-04-01";
                break;
            case 2:
                $date = "$year-07-01";
                break;
            case 3:
                $date = "$year-10-01";
                break;
            case 4:
            default:
                $date = "$nextYear-01-01";
                break;
        }

        /** @var DateTimeImmutable $retVal */
        $retVal = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        return $retVal;
    }

    public static function filenameToUrl(string $filename): string
    {
        if (strpos($filename, self::UPLOAD_DIR) === 0)
        {
            $parentDir = dirname(self::UPLOAD_DIR);
            return substr($filename, strlen($parentDir));
        }

        return $filename;
    }

    public static function ensureDirectoryExists(string $dir): void
    {
        if (!is_dir($dir) && !self::createDir($dir))
        {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
    }

    public static function deleteFile(string $filename): bool
    {
        try
        {
            @unlink($filename);
        }
        catch (FilesystemException)
        {
            return false;
        }

        return true;
    }

    public static function getSlug(string $url): string
    {
        $firstPass = preg_replace('/[^0-9a-z\-]+/', '-', strtolower($url));
        /** @var string $dedoubled */
        $dedoubled = str_replace('--', '-', $firstPass);
        return $dedoubled;
    }

    public static function formatSize(int $size): string
    {
        $power = 0;
        $maxPower = count(self::BYTE_POSTFIXES) - 1;
        for (; $power < $maxPower; $power++)
        {
            if ($size < 1024)
            {
                break;
            }

            $size /= 1024;
        }

        $postfix = self::BYTE_POSTFIXES[$power];
        return number_format($size, 1) . $postfix;
    }

    public static function fetch(string $url): string
    {
        $options  = ['http' => ['user_agent' => CyndaronInfo::PRODUCT_NAME . ' ' . CyndaronInfo::ENGINE_VERSION]];
        $context  = stream_context_create($options);
        return file_get_contents($url, context: $context);
    }
}
