<?php
namespace Cyndaron\Category;

use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;

class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    const TYPE = 'category';

    protected function prepare(RequestParameters $post)
    {
        $categoryId = $post->getInt('categoryId');

        $category = new Category($this->id);
        $category->loadIfIdIsSet();
        $category->name = $post->getHTML('titel');
        $category->description = $this->parseTextForInlineImages($post->getHTML('artikel'));
        $category->viewMode = $post->getInt('viewMode');
        $category->categoryId = ($categoryId === 0) ? null : $categoryId;
        $category->showBreadcrumbs = $post->getBool('showBreadcrumbs');
        $category->save();

        User::addNotification('Categorie bewerkt.');
        $this->returnUrl = '/category/' . $this->id;
    }
}