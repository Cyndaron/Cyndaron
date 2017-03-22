<?php
namespace Cyndaron;

require_once __DIR__ . '/../functies.gebruikers.php';
require_once __DIR__ . '/../functies.pagina.php';

class BewerkStatischePagina extends Bewerk
{
    protected function prepare()
    {
        $this->type = 'sub';
        $actie = geefGetVeilig('actie');

        if ($actie == 'bewerken')
        {
            $titel = geefPostOnveilig('titel');
            $tekst = parseTextForInlineImages(geefPostOnveilig('artikel'));
            $reacties_aan = geefPostOnveilig('reacties_aan');
            $categorieid = geefPostOnveilig('categorieid');

            if (!$categorieid)
                $categorieid = '0';

            if ($this->id > 0) // Als het id is meegegeven bestond de sub al. In dat geval moet er geüpdatet worden. Anders moet het toegevoegd worden onder vermelding van een naam/titel.
            {
                wijzigSub($this->id, $titel, $tekst, $reacties_aan, $categorieid);
            }
            else
            {
                $this->id = nieuweSub($titel, $tekst, $reacties_aan, $categorieid);
            }

            nieuweMelding('Pagina bewerkt.');
            $this->returnUrl = 'toonsub.php?id=' . $this->id;
        }
    }
}
