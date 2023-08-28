<?php
declare(strict_types=1);

namespace Cyndaron\Menu;

final class Menu
{
    /**
     * @return MenuItem[]
     */
    public static function get(): array
    {
        return MenuItem::fetchAll();
    }
}
