<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Newsletter;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;

class Subscriber extends Model
{
    public const TABLE = 'newsletter_subscriber';

    #[DatabaseField]
    public string $name = '';
    #[DatabaseField]
    public string $email = '';
    #[DatabaseField]
    public bool $confirmed = false;
}
