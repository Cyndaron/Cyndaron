<?php
namespace Cyndaron\Widget;

class Knop extends Widget
{
    public function __construct($soort, $link, $beschrijving = null, $tekst = null, $formaat = 20)
    {
        switch ($soort)
        {
            case 'nieuw':
                $pictogram = 'plus';
                break;
            case 'bewerken':
                $pictogram = 'pencil';
                break;
            case 'verwijderen':
                $pictogram = 'trash';
                break;
            case 'vorigeversie':
                $pictogram = 'vorige-versie';
                break;
            case 'aanmenutoevoegen':
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
        $this->code = sprintf('<a class="btn btn-default %s" href="%s" %s><span class="glyphicon glyphicon-%s"></span>%s</a>', $btnClass, $link, $title, $pictogram, $tekstNaPictogram);
    }
}