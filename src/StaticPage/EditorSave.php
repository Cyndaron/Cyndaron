<?php
namespace Cyndaron\StaticPage;

use Cyndaron\Category\CategoryRepository;
use Cyndaron\DBAL\Model;
use Cyndaron\Imaging\ImageExtractor;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserSession;
use Symfony\Component\HttpFoundation\Request;
use function trim;

final class EditorSave extends \Cyndaron\Editor\EditorSave
{
    public function __construct(
        private readonly RequestParameters $post,
        private readonly Request           $request,
        private readonly ImageExtractor    $imageExtractor,
        private readonly UserSession       $userSession,
        private readonly StaticPageRepository $staticPageRepository,
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    public function save(int|null $id): Model
    {
        $titel = $this->post->getHTML('titel');
        $text = $this->imageExtractor->process($this->post->getHTML('artikel'));
        $enableComments = $this->post->getBool('enableComments');
        $showBreadcrumbs = $this->post->getBool('showBreadcrumbs');
        $tags = trim($this->post->getSimpleString('tags'), "; \t\n\r\0\x0B");

        $model = $this->staticPageRepository->fetchOrCreate($id);
        $model->name = $titel;
        $model->blurb = $this->post->getHTML('blurb');
        $model->text = $text;
        $model->enableComments = $enableComments;
        $model->showBreadcrumbs = $showBreadcrumbs;
        $model->tags = $tags;
        $this->saveHeaderAndPreviewImage($model, $this->post, $this->request);
        try
        {
            $this->staticPageRepository->save($model);
            $this->saveCategories($this->staticPageRepository, $this->categoryRepository, $model, $this->post);

            $this->userSession->addNotification('Pagina bewerkt.');
        }
        catch (\PDOException)
        {
            $this->userSession->addNotification('Pagina opslaan mislukt');
        }

        return $model;
    }
}
