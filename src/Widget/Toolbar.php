<?php
declare (strict_types = 1);

namespace Cyndaron\Widget;

class Toolbar extends Widget
{
    public function __construct(?string $left, ?string $center, ?string $right)
    {
        $this->code = '
        <nav class="navbar toolbar">
            <form class="form-inline">
                ' . $left . '
            </form>
            <form class="form-inline">
                ' . $center . '
            </form>
            <form class="form-inline">
                ' . $right . '
            </form>
        </nav>
        ';
    }
}