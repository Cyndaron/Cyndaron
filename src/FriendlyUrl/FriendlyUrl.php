<?php
namespace Cyndaron\FriendlyUrl;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;

final class FriendlyUrl extends Model
{
    public const TABLE = 'friendlyurls';

    #[DatabaseField]
    public string $name = '';
    #[DatabaseField]
    public string $target = '';
}
