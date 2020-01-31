<?php
namespace Cyndaron\Widget;

class PageTabs extends Widget
{
    public function __construct(array $subpages, string $urlPrefix = '', string $currentPage = '')
    {
        $this->code = '<ul class="nav nav-tabs">';

        foreach($subpages as $link => $title)
        {
            $this->code .= sprintf(
                '<li role="presentation" class="nav-item"><a class="nav-link %s" href="%s">%s</a></li>',
                ($link === $currentPage) ? ' active' : '',
                rtrim($urlPrefix . $link, '/'),
                $title
            );
        }

        $this->code .= '</ul>';
    }
}