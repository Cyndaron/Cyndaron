<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\Imaging\ImageExtractor;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserSession;
use Symfony\Component\HttpFoundation\Request;
use function assert;

final class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    public const TYPE = 'photoalbum';

    public function __construct(
        private readonly RequestParameters $post,
        private readonly Request $request,
        private readonly ImageExtractor $imageExtractor,
    ) {
    }

    public function save(int|null $id): int
    {
        $photoalbum = new Photoalbum($id);
        $photoalbum->loadIfIdIsSet();
        $photoalbum->name = $this->post->getHTML('titel');
        $photoalbum->blurb = $this->post->getHTML('blurb');
        $photoalbum->notes = $this->imageExtractor->process($this->post->getHTML('artikel'));
        $photoalbum->showBreadcrumbs = $this->post->getBool('showBreadcrumbs');
        $photoalbum->hideFromOverview = $this->post->getBool('hideFromOverview');
        $photoalbum->viewMode = $this->post->getInt('viewMode');
        $photoalbum->thumbnailWidth = $this->post->getInt('thumbnailWidth');
        $photoalbum->thumbnailHeight = $this->post->getInt('thumbnailHeight');
        $this->saveHeaderAndPreviewImage($photoalbum, $this->post, $this->request);
        $photoalbum->save();
        $this->saveCategories($photoalbum, $this->post);

        UserSession::addNotification('Fotoalbum bewerkt.');

        assert($photoalbum->id !== null);
        $this->returnUrl = '/photoalbum/' . $photoalbum->id;
        return $photoalbum->id;
    }
}
