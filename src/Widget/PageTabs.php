<?php
namespace Cyndaron\Widget;

class PageTabs extends Widget
{
    public function __construct($subpages, $urlPrefix = '', $currentPage = '')
    {
        $this->code = '<ul class="nav nav-tabs">';

        foreach($subpages as $link => $title)
        {
            $class = ($link == $currentPage) ? ' class="active"' : '';
            $this->code .= '<li role="presentation"' . $class . '><a class="nav-link" href="' . rtrim($urlPrefix . $link, '/') . '">' . $title . '</a></li>';
        }

        $this->code .= '</ul>';
    }
}