<?php
namespace Cyndaron\Category;

use Cyndaron\Request;
use Cyndaron\User\User;
use Cyndaron\Util;

class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    protected $type = 'category';

    protected function prepare()
    {
        $titel = Request::geefPostOnveilig('titel');
        $beschrijving = $this->parseTextForInlineImages(Request::geefPostOnveilig('artikel'));
        $alleentitel = Util::parseCheckBoxAlsBool(Request::geefPostOnveilig('alleentitel'));
        $categorieId = intval(Request::geefPostVeilig('categorieid'));

        if ($this->id > 0) // Als het id is meegegeven bestond de categorie al.
        {
            CategoryModel::wijzigCategorie($this->id, $titel, $alleentitel, $beschrijving, $categorieId);
        }
        else
        {
            $this->id = CategoryModel::nieuweCategorie($titel, $alleentitel, $beschrijving, $categorieId);
        }

        User::addNotification('Categorie bewerkt.');
        $this->returnUrl = '/category/' . $this->id;
    }
}