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
        $titel = Request::unsafePost('titel');
        $beschrijving = $this->parseTextForInlineImages(Request::unsafePost('artikel'));
        $alleentitel = (bool)Request::unsafePost('alleentitel');
        $categorieId = intval(Request::post('categorieid'));
        $showBreadcrumbs = (bool)Request::post('showBreadcrumbs');

        if ($this->id > 0) // Als het id is meegegeven bestond de categorie al.
        {
            Category::edit($this->id, $titel, $alleentitel, $beschrijving, $categorieId, $showBreadcrumbs);
        }
        else
        {
            $this->id = Category::create($titel, $alleentitel, $beschrijving, $categorieId);
        }

        User::addNotification('Categorie bewerkt.');
        $this->returnUrl = '/category/' . $this->id;
    }
}