<?php
namespace Cyndaron\FriendlyUrl;

use Cyndaron\Model;

class FriendlyUrl extends Model
{
    public const TABLE = 'friendlyurls';
    public const TABLE_FIELDS = ['name', 'target'];

    public string $name = '';
    public string $target = '';
}
