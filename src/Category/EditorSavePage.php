<?php
namespace Cyndaron\Category;

use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;

class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    public const TYPE = 'category';

    protected function prepare(RequestParameters $post)
    {
        $category = new Category($this->id);
        $category->loadIfIdIsSet();
        $category->name = $post->getHTML('titel');
        $category->image = $post->getUrl('image');
        $category->previewImage = $post->getUrl('previewImage');
        $category->blurb = $post->getHTML('blurb');
        $category->description = $this->parseTextForInlineImages($post->getHTML('artikel'));
        $category->viewMode = $post->getInt('viewMode');
        $category->showBreadcrumbs = $post->getBool('showBreadcrumbs');
        $this->saveCategories($category, $post);
        $category->save();

        User::addNotification('Categorie bewerkt.');
        $this->returnUrl = '/category/' . $this->id;
    }
}
