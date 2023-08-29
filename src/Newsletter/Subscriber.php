<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Newsletter;

use Cyndaron\DBAL\Model;

class Subscriber extends Model
{
    public const TABLE = 'newsletter_subscriber';
    public const TABLE_FIELDS = ['name', 'email', 'confirmed'];

    public string $name = '';
    public string $email = '';
    public bool $confirmed = false;
}
