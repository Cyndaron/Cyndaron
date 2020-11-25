<?php
namespace Cyndaron\FriendlyUrl;

use Cyndaron\DBConnection;
use Cyndaron\Model;
use function ltrim;

final class FriendlyUrl extends Model
{
    public const TABLE = 'friendlyurls';
    public const TABLE_FIELDS = ['name', 'target'];

    public string $name = '';
    public string $target = '';

    public static function fetchByName(string $name): ?self
    {
        $result = DBConnection::doQueryAndFetchFirstRow('SELECT * FROM friendlyurls WHERE name=?', [ltrim($name, '/')]);
        if (empty($result))
        {
            return null;
        }

        $ret = new self($result['id']);
        $ret->updateFromArray($result);
        return $ret;
    }
}
