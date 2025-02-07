<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\Imaging\ImageExtractor;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserSession;

final class EditorSavePhoto extends \Cyndaron\Editor\EditorSave
{
    public const TYPE = 'photo';

    public function __construct(
        private readonly RequestParameters $post,
        private readonly ImageExtractor $imageExtractor,
        private readonly UserSession $userSession,
        private readonly PhotoalbumCaptionRepository $photoalbumCaptionRepository
    ) {
    }

    public function save(int|null $id): int
    {
        $photoalbumId = $this->post->getInt('photoalbumId');
        $hash = $this->post->getAlphaNum('hash');
        $caption = $this->imageExtractor->process($this->post->getHTML('artikel'));

        $object = $this->photoalbumCaptionRepository->fetchByHash($hash);
        if ($object === null)
        {
            $object = new PhotoalbumCaption();
        }
        $object->caption = $caption;
        $this->photoalbumCaptionRepository->save($object);

        $this->userSession->addNotification('Bijschrift bewerkt.');
        $this->returnUrl = '/photoalbum/' . $photoalbumId;

        return -1;
    }
}
