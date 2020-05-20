<?php
declare(strict_types=1);

namespace Cyndaron\Menu;

class Menu
{
    public static function get(): array
    {
        return MenuItem::fetchAll();
    }
}
