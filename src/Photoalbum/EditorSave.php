<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Cyndaron\Category\CategoryRepository;
use Cyndaron\DBAL\Model;
use Cyndaron\Imaging\ImageExtractor;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserSession;
use Symfony\Component\HttpFoundation\Request;

final class EditorSave extends \Cyndaron\Editor\EditorSave
{
    public function __construct(
        private readonly RequestParameters $post,
        private readonly Request           $request,
        private readonly ImageExtractor    $imageExtractor,
        private readonly UserSession       $userSession,
        private readonly PhotoalbumRepository $photoalbumRepository,
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    public function save(int|null $id): Model
    {
        $photoalbum = $this->photoalbumRepository->fetchOrCreate($id);
        $photoalbum->name = $this->post->getHTML('titel');
        $photoalbum->blurb = $this->post->getHTML('blurb');
        $photoalbum->notes = $this->imageExtractor->process($this->post->getHTML('artikel'));
        $photoalbum->showBreadcrumbs = $this->post->getBool('showBreadcrumbs');
        $photoalbum->hideFromOverview = $this->post->getBool('hideFromOverview');
        $photoalbum->viewMode = $this->post->getInt('viewMode');
        $photoalbum->thumbnailWidth = $this->post->getInt('thumbnailWidth');
        $photoalbum->thumbnailHeight = $this->post->getInt('thumbnailHeight');
        $this->saveHeaderAndPreviewImage($photoalbum, $this->post, $this->request);
        $this->photoalbumRepository->save($photoalbum);
        $this->saveCategories($this->photoalbumRepository, $this->categoryRepository, $photoalbum, $this->post);

        $this->userSession->addNotification('Fotoalbum bewerkt.');

        return $photoalbum;
    }
}
