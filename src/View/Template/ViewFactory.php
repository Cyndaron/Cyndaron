<?php
declare(strict_types=1);

namespace Cyndaron\View\Template;

final class ViewFactory extends \Illuminate\View\Factory
{
    protected function normalizeName($name): string
    {
        return $name;
    }
}
