<?php
namespace Cyndaron;


class BewerkStatischePagina extends Bewerk
{
    protected function prepare()
    {
        $this->type = 'sub';
        $actie = Request::geefGetVeilig('actie');

        if ($actie == 'bewerken')
        {
            $titel = Request::geefPostOnveilig('titel');
            $tekst = $this->parseTextForInlineImages(Request::geefPostOnveilig('artikel'));
            $reacties_aan = Request::geefPostOnveilig('reacties_aan');
            $categorieid = intval(Request::geefPostOnveilig('categorieid'));

            $model = new StatischePaginaModel($this->id);
            $model->setNaam($titel);
            $model->setTekst($tekst);
            $model->setReactiesAan($reacties_aan);
            $model->setCategorieId($categorieid);
            $model->opslaan();
            $this->id = $model->getId();

            Gebruiker::nieuweMelding('Pagina bewerkt.');
            $this->returnUrl = 'toonsub.php?id=' . $this->id;
        }
    }
}
