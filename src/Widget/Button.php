<?php
namespace Cyndaron\Widget;

class Button extends Widget
{
    public function __construct(string $kind, string $link, string $description = '', $text = null, int $size = 20)
    {
        [$icon, $btnClass] = $this->getIconAndClass($kind);

        if ($size === 16)
        {
            $btnClass .= ' btn-sm';
        }

        $title = $description ? sprintf('title="%s"', $description) : '';
        $textAfterIcon = $text ? " $text" : '';
        $this->code = sprintf('<a class="btn %s" href="%s" %s><span class="glyphicon glyphicon-%s"></span>%s</a>', $btnClass, $link, $title, $icon, $textAfterIcon);
    }/**
 * @param string $kind
 * @return string[]
 */
    private function getIconAndClass(string $kind): array
    {
        $btnClass = 'btn-outline-cyndaron';

        switch ($kind)
        {
            case 'new':
                $icon = 'plus';
                $btnClass = 'btn-success';
                break;
            case 'edit':
                $icon = 'pencil';
                break;
            case 'delete':
                $icon = 'trash';
                $btnClass = 'btn-danger';
                break;
            case 'lastversion':
                $icon = 'lastversion';
                break;
            case 'addtomenu':
                $icon = 'bookmark';
                break;
            default:
                $icon = $kind;
        }
        return [$icon, $btnClass];
    }
}