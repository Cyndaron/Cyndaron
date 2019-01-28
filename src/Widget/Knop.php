<?php
namespace Cyndaron\Widget;

class Knop extends Widget
{
    public function __construct($soort, $link, $beschrijving = null, $tekst = null, $formaat = 20)
    {
        switch ($soort)
        {
            case 'new':
                $pictogram = 'plus';
                break;
            case 'edit':
                $pictogram = 'pencil';
                break;
            case 'delete':
                $pictogram = 'trash';
                break;
            case 'lastversion':
                $pictogram = 'lastversion';
                break;
            case 'addtomenu':
                $pictogram = 'bookmark';
                break;
            default:
                $pictogram = $soort;
        }

        switch ($formaat)
        {
            case 16:
                $btnClass = 'btn-sm';
                break;
            default:
                $btnClass = '';
        }

        $title = $beschrijving ? 'title="' . $beschrijving . '"' : '';
        $tekstNaPictogram = $tekst ? ' ' . $tekst : '';
        $this->code = sprintf('<a class="btn btn-outline-cyndaron %s" href="%s" %s><span class="glyphicon glyphicon-%s"></span>%s</a>', $btnClass, $link, $title, $pictogram, $tekstNaPictogram);
    }
}