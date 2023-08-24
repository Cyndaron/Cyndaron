<?php
declare(strict_types=1);

namespace Cyndaron\MailAdmin;

use Cyndaron\Module\Routes;

class Module implements Routes
{
    public function routes(): array
    {
        return [
            'mailadmin' => Controller::class,
        ];
    }
}
