<?php
namespace Cyndaron\Widget;

class Pagination extends Widget
{
    public function __construct(string $link, int $numPages, int $currentPage, int $offset = 0)
    {
        if ($numPages === 1)
        {
            return;
        }

        $this->code = '<div class="lettermenu"><ul class="pagination">';

        $lastPageNum = 0;
        foreach ($this->determinePages($numPages, $currentPage) as $i)
        {
            if ($i > $numPages)
            {
                break;
            }

            if ($i < 1)
            {
                continue;
            }

            if ($lastPageNum !== $i - 1)
            {
                $this->code .= '<li><span>...</span></li>';
            }

            $class = $i === $currentPage ? 'class="active"' : '';
            $this->code .= sprintf('<li %s><a href="%s%d">%d</a></li>', $class, $link, ($i + $offset), $i);

            $lastPageNum = $i;
        }

        $this->code .= '</ul></div>';
    }

    /**
     * @param int $numPages
     * @param int $currentPage
     * @return int[]
     */
    public function determinePages(int $numPages, int $currentPage): array
    {
        $pagesToShow = [
            1, 2, 3,
            $numPages, $numPages - 1, $numPages - 2,
            $currentPage - 2, $currentPage - 1, $currentPage, $currentPage + 1, $currentPage + 2,
        ];

        if ($currentPage === 7)
        {
            $pagesToShow[] = 4;
        }
        if ($numPages - $currentPage === 6)
        {
            $pagesToShow[] = $numPages - 3;
        }

        $pagesToShow = array_unique($pagesToShow);
        natsort($pagesToShow);
        return $pagesToShow;
    }
}