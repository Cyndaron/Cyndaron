<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBAL\Repository;
use Cyndaron\Imaging\ImageExtractor;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserSession;
use Symfony\Component\HttpFoundation\Request;
use function trim;
use function assert;

final class EditorSave extends \Cyndaron\Editor\EditorSave
{
    public const TYPE = 'sub';

    public function __construct(
        private readonly RequestParameters $post,
        private readonly Request $request,
        private readonly ImageExtractor $imageExtractor,
        private readonly UserSession $userSession,
        private readonly Repository $repository,
    ) {
    }

    public function save(int|null $id): int
    {
        $titel = $this->post->getHTML('titel');
        $text = $this->imageExtractor->process($this->post->getHTML('artikel'));
        $enableComments = $this->post->getBool('enableComments');
        $showBreadcrumbs = $this->post->getBool('showBreadcrumbs');
        $tags = trim($this->post->getSimpleString('tags'), "; \t\n\r\0\x0B");

        $model = $this->repository->fetchOrCreate(StaticPageModel::class, $id);
        $model->name = $titel;
        $model->blurb = $this->post->getHTML('blurb');
        $model->text = $text;
        $model->enableComments = $enableComments;
        $model->showBreadcrumbs = $showBreadcrumbs;
        $model->tags = $tags;
        $this->saveHeaderAndPreviewImage($model, $this->post, $this->request);
        try
        {
            $this->repository->save($model);
            $this->saveCategories($model, $this->post);

            $this->userSession->addNotification('Pagina bewerkt.');
            $this->returnUrl = '/sub/' . $model->id;
        }
        catch (\PDOException)
        {
            $this->userSession->addNotification('Pagina opslaan mislukt');
        }

        assert($model->id !== null);
        return $model->id;
    }
}
