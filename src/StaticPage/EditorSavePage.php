<?php
namespace Cyndaron\StaticPage;

use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;
use Symfony\Component\HttpFoundation\Request;
use function trim;

final class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    public const TYPE = 'sub';

    protected function prepare(RequestParameters $post, Request $request): void
    {
        $titel = $post->getHTML('titel');
        $text = $this->parseTextForInlineImages($post->getHTML('artikel'));
        $enableComments = $post->getBool('enableComments');
        $showBreadcrumbs = $post->getBool('showBreadcrumbs');
        $tags = trim($post->getSimpleString('tags'), "; \t\n\r\0\x0B");

        $model = new StaticPageModel($this->id);
        $model->loadIfIdIsSet();
        $model->name = $titel;
        $model->blurb = $post->getHTML('blurb');
        $model->text = $text;
        $model->enableComments = $enableComments;
        $model->showBreadcrumbs = $showBreadcrumbs;
        $model->tags = $tags;
        $this->saveHeaderAndPreviewImage($model, $post, $request);
        if ($model->save())
        {
            $this->saveCategories($model, $post);
            $this->id = $model->id;

            User::addNotification('Pagina bewerkt.');
            $this->returnUrl = '/sub/' . $this->id;
        }
        else
        {
            User::addNotification('Pagina opslaan mislukt');
        }
    }
}
