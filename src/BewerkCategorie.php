<?php
namespace Cyndaron;

require_once __DIR__ . '/../functies.gebruikers.php';
require_once __DIR__ . '/../functies.pagina.php';

class BewerkCategorie extends Bewerk
{
    protected function prepare()
    {
        $this->type = 'categorie';
        $actie = geefGetVeilig('actie');

        if ($actie == 'bewerken')
        {
            $titel = geefPostOnveilig('titel');
            $beschrijving = parseTextForInlineImages(geefPostOnveilig('artikel'));
            $alleentitel = parseCheckBoxAlsBool(geefPostOnveilig('alleentitel'));

            if ($this->id > 0) // Als het id is meegegeven bestond de categorie al.
            {
                wijzigCategorie($this->id, $titel, $alleentitel, $beschrijving);
            }
            else
            {
                $this->id = nieuweCategorie($titel, $alleentitel, $beschrijving);
            }

            nieuweMelding('Categorie bewerkt.');
            $this->returnUrl = 'tooncategorie.php?id=' . $this->id;
        }

    }
}