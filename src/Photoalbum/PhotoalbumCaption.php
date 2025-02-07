<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\DBConnection;
use Cyndaron\DBAL\Model;

final class PhotoalbumCaption extends Model
{
    public const TABLE = 'photoalbum_captions';

    #[DatabaseField]
    public string $hash;
    #[DatabaseField]
    public string $caption;

    public static function create(string $hash, string $caption): bool
    {
        DBConnection::getPDO()->doQueryAndFetchOne('DELETE FROM photoalbum_captions WHERE hash = ?', [$hash]);
        return (bool)DBConnection::getPDO()->doQueryAndFetchOne('INSERT INTO photoalbum_captions(hash,caption) VALUES (?,?)', [$hash, $caption]);
    }

    public static function fetchByHash(string $hash): PhotoalbumCaption|null
    {
        return self::fetch(['hash = ?'], [$hash]);
    }
}
