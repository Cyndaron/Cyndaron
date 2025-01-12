<?php
namespace Cyndaron\FriendlyUrl;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;
use function ltrim;

final class FriendlyUrl extends Model
{
    public const TABLE = 'friendlyurls';

    #[DatabaseField]
    public string $name = '';
    #[DatabaseField]
    public string $target = '';

    public static function fetchByName(string $name): self|null
    {
        return self::fetch(['name = ?'], [ltrim($name, '/')]);
    }
}
