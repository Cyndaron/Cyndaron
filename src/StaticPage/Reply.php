<?php
declare(strict_types=1);

namespace Cyndaron\StaticPage;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;

class Reply extends Model
{
    public const TABLE = 'sub_replies';

    #[DatabaseField(dbName: 'subId')]
    public StaticPageModel $sub;

    #[DatabaseField]
    public string $author;

    #[DatabaseField]
    public string $text;
}
