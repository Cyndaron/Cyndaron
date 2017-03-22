<?php
namespace Cyndaron;

require_once __DIR__ . '/../functies.pagina.php';

class BewerkCategorie extends Bewerk
{
    protected function prepare()
    {
        $this->type = 'categorie';
        $actie = Request::geefGetVeilig('actie');

        if ($actie == 'bewerken')
        {
            $titel = Request::geefPostOnveilig('titel');
            $beschrijving = $this->parseTextForInlineImages(Request::geefPostOnveilig('artikel'));
            $alleentitel = parseCheckBoxAlsBool(Request::geefPostOnveilig('alleentitel'));

            if ($this->id > 0) // Als het id is meegegeven bestond de categorie al.
            {
                wijzigCategorie($this->id, $titel, $alleentitel, $beschrijving);
            }
            else
            {
                $this->id = nieuweCategorie($titel, $alleentitel, $beschrijving);
            }

            Gebruiker::nieuweMelding('Categorie bewerkt.');
            $this->returnUrl = 'tooncategorie.php?id=' . $this->id;
        }

    }
}