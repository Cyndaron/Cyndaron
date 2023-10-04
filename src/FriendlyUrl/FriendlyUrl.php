<?php
namespace Cyndaron\FriendlyUrl;

use Cyndaron\DBAL\Model;
use function ltrim;

final class FriendlyUrl extends Model
{
    public const TABLE = 'friendlyurls';
    public const TABLE_FIELDS = ['name', 'target'];

    public string $name = '';
    public string $target = '';

    public static function fetchByName(string $name): self|null
    {
        return self::fetch(['name = ?'], [ltrim($name, '/')]);
    }
}
