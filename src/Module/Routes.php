<?php
declare(strict_types=1);

namespace Cyndaron\Module;

use Cyndaron\Routing\Controller;

interface Routes
{
    /**
     * Additional routes in the form of
     * ['route' => Controller::class]
     *
     * @return array<string, class-string<Controller>>
     */
    public function routes(): array;
}
