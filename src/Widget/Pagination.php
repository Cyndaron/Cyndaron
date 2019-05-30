<?php
namespace Cyndaron\Widget;

class Pagination extends Widget
{
    public function __construct($link, $numPages, $currentPage, $offset = 0)
    {
        if ($numPages == 1)
        {
            return;
        }

        $this->code = '<div class="lettermenu"><ul class="pagination">';
        $teTonenPaginas = [
            1, 2, 3,
            $numPages, $numPages - 1, $numPages - 2,
            $currentPage - 2, $currentPage - 1, $currentPage, $currentPage + 1, $currentPage + 2,
        ];

        if ($currentPage == 7)
        {
            $teTonenPaginas[] = 4;
        }
        if ($numPages - $currentPage == 6)
        {
            $teTonenPaginas[] = $numPages - 3;
        }

        $teTonenPaginas = array_unique($teTonenPaginas);
        natsort($teTonenPaginas);

        $vorigePaginanummer = 0;
        foreach ($teTonenPaginas as $i)
        {
            if ($i > $numPages)
            {
                break;
            }

            if ($i < 1)
            {
                continue;
            }

            if ($vorigePaginanummer != $i - 1)
            {
                $this->code .= '<li><span>...</span></li>';
            }

            $class = '';
            if ($i == $currentPage)
            {
                $class = 'class="active"';
            }

            $this->code .= sprintf('<li %s><a href="%s%d">%d</a></li>', $class, $link, ($i + $offset), $i);

            $vorigePaginanummer = $i;
        }

        $this->code .= '</ul></div>';
    }
}