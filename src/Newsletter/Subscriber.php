<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the ISC License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Newsletter;

use Cyndaron\Model;

class Subscriber extends Model
{
    public const TABLE = 'newsletter_subscriber';
    public const TABLE_FIELDS = ['name', 'email'];

    public string $name = '';
    public string $email = '';
}
