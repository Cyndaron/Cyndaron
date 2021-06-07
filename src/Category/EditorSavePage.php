<?php
namespace Cyndaron\Category;

use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;
use Symfony\Component\HttpFoundation\Request;

final class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    public const TYPE = 'category';

    protected function prepare(RequestParameters $post, Request $request): void
    {
        $category = new Category($this->id);
        $category->loadIfIdIsSet();
        $category->name = $post->getHTML('titel');
        $category->blurb = $post->getHTML('blurb');
        $category->description = $this->parseTextForInlineImages($post->getHTML('artikel'));
        $category->viewMode = $post->getInt('viewMode');
        $category->showBreadcrumbs = $post->getBool('showBreadcrumbs');
        $this->saveHeaderAndPreviewImage($category, $post, $request);
        $category->save();
        $this->saveCategories($category, $post);

        User::addNotification('Categorie bewerkt.');
        $this->returnUrl = '/category/' . $this->id;
    }
}
