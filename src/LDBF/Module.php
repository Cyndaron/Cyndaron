<?php
namespace Cyndaron\LDBF;

use Cyndaron\Module\Routes;

final class Module implements Routes
{
    /**
     * @inheritDoc
     */
    public function routes(): array
    {
        return [
            'ldbf' => Controller::class,
        ];
    }
}
