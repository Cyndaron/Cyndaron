<?php
declare(strict_types=1);

namespace Cyndaron\System;

use Cyndaron\Module\Routes;

final class Module implements Routes
{
    public function routes(): array
    {
        return [
            'system' => [
                AboutPage::class,
                ConfigPage::class,
                ChecksPage::class,
                PHPInfoPage::class,
            ]
        ];
    }
}
