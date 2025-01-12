<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\DBAL\Repository;
use Cyndaron\Imaging\ImageExtractor;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserSession;
use Symfony\Component\HttpFoundation\Request;
use function assert;

final class EditorSave extends \Cyndaron\Editor\EditorSave
{
    public const TYPE = 'photoalbum';

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
        $photoalbum = $this->repository->fetchOrCreate(Photoalbum::class, $id);
        $photoalbum->name = $this->post->getHTML('titel');
        $photoalbum->blurb = $this->post->getHTML('blurb');
        $photoalbum->notes = $this->imageExtractor->process($this->post->getHTML('artikel'));
        $photoalbum->showBreadcrumbs = $this->post->getBool('showBreadcrumbs');
        $photoalbum->hideFromOverview = $this->post->getBool('hideFromOverview');
        $photoalbum->viewMode = $this->post->getInt('viewMode');
        $photoalbum->thumbnailWidth = $this->post->getInt('thumbnailWidth');
        $photoalbum->thumbnailHeight = $this->post->getInt('thumbnailHeight');
        $this->saveHeaderAndPreviewImage($photoalbum, $this->post, $this->request);
        $this->repository->save($photoalbum);
        $this->saveCategories($photoalbum, $this->post);

        $this->userSession->addNotification('Fotoalbum bewerkt.');

        assert($photoalbum->id !== null);
        $this->returnUrl = '/photoalbum/' . $photoalbum->id;
        return $photoalbum->id;
    }
}
