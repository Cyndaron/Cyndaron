<?php
namespace Cyndaron\Widget;

class Paginering extends Widget
{
    public function __construct($link, $aantalPaginas, $huidigePagina, $verschuiving = 0)
    {
        if ($aantalPaginas == 1)
        {
            return;
        }

        $this->code = '<div class="lettermenu"><ul class="pagination">';
        $teTonenPaginas = [
            1, 2, 3,
            $aantalPaginas, $aantalPaginas - 1, $aantalPaginas - 2,
            $huidigePagina - 2, $huidigePagina - 1, $huidigePagina, $huidigePagina + 1, $huidigePagina + 2,
        ];

        if ($huidigePagina == 7)
        {
            $teTonenPaginas[] = 4;
        }
        if ($aantalPaginas - $huidigePagina == 6)
        {
            $teTonenPaginas[] = $aantalPaginas - 3;
        }

        $teTonenPaginas = array_unique($teTonenPaginas);
        natsort($teTonenPaginas);

        $vorigePaginanummer = 0;
        foreach ($teTonenPaginas as $i)
        {
            if ($i > $aantalPaginas)
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
            if ($i == $huidigePagina)
            {
                $class = 'class="active"';
            }

            $this->code .= sprintf('<li %s><a href="%s%d">%d</a></li>', $class, $link, ($i + $verschuiving), $i);

            $vorigePaginanummer = $i;
        }

        $this->code .= '</ul></div>';
    }
}