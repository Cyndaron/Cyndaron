<?php
namespace Cyndaron\Category;

use Cyndaron\Imaging\ImageExtractor;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserSession;
use Symfony\Component\HttpFoundation\Request;
use function assert;

final class EditorSave extends \Cyndaron\Editor\EditorSave
{
    public const TYPE = 'category';

    public function __construct(
        private readonly RequestParameters $post,
        private readonly Request $request,
        private readonly ImageExtractor $imageExtractor,
    ) {
    }

    public function save(int|null $id): int
    {
        $category = new Category($id);
        $category->loadIfIdIsSet();
        $category->name = $this->post->getHTML('titel');
        $category->blurb = $this->post->getHTML('blurb');
        $category->description = $this->imageExtractor->process($this->post->getHTML('artikel'));
        $category->viewMode = ViewMode::from($this->post->getInt('viewMode'));
        $category->showBreadcrumbs = $this->post->getBool('showBreadcrumbs');
        $this->saveHeaderAndPreviewImage($category, $this->post, $this->request);
        $category->save();
        $this->saveCategories($category, $this->post);

        UserSession::addNotification('Categorie bewerkt.');

        assert($category->id !== null);
        $this->returnUrl = '/category/' . $category->id;
        return $category->id;
    }
}
