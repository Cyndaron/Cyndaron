<?php
namespace Cyndaron\Category;

use Cyndaron\Request;
use Cyndaron\User\User;

class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    const TYPE = 'category';

    protected function prepare()
    {
        $category = new Category($this->id);
        $category->loadIfIdIsSet();
        $category->name = Request::unsafePost('titel');
        $category->description = $this->parseTextForInlineImages(Request::unsafePost('artikel'));
        $category->viewMode = Request::unsafePost('alleentitel');
        $category->categoryId = intval(Request::post('categoryId'));
        $category->showBreadcrumbs = (bool)Request::post('showBreadcrumbs');
        $category->save();

        User::addNotification('Categorie bewerkt.');
        $this->returnUrl = '/category/' . $this->id;
    }
}