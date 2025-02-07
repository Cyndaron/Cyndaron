<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;

final class PhotoalbumCaption extends Model
{
    public const TABLE = 'photoalbum_captions';

    #[DatabaseField]
    public string $hash;
    #[DatabaseField]
    public string $caption;


}
