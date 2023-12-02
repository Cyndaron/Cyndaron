<?php
declare(strict_types=1);

namespace Cyndaron\Category;

enum ViewMode : int
{
    case Regular = 0;
    case Titles = 1;
    case Blog = 2;
    case Portfolio = 3;
    case Horizontal = 4;

    public function getDescription(): string
    {
        return match ($this)
        {
            self::Regular => 'Samenvatting',
            self::Titles => 'Alleen titels',
            self::Blog => 'Blog',
            self::Portfolio => 'Portfolio',
            self::Horizontal => 'Horizontaal',
        };
    }
}
