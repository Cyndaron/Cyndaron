<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Cyndaron\Page\Page;
use Cyndaron\User\User;
use Cyndaron\View\Renderer\TextRenderer;
use Cyndaron\View\Template\TemplateRenderer;

final class PhotoalbumPage extends Page
{
    public function __construct(Photoalbum $album, PhotoalbumRepository $photoalbumRepository, TextRenderer $textRenderer, User|null $currentUser, int $viewMode = Photoalbum::VIEWMODE_REGULAR)
    {
        $this->model = $album;
        $this->category = $photoalbumRepository->getFirstLinkedCategory($album);
        $this->title = $album->name;
        $canUpload = $currentUser !== null && $currentUser->hasRight(Photoalbum::RIGHT_UPLOAD);

        if ($viewMode === Photoalbum::VIEWMODE_REGULAR)
        {
            $this->addScript('/js/lightbox.min.js');

            $photos = Photo::fetchAllByAlbum($album);
            $this->templateVars['model'] = $album;
            $this->templateVars['photos'] = $photos;
            $this->templateVars['pageImage'] = $album->getImage();
        }

        $this->templateVars['canUpload'] = $canUpload;
        $this->templateVars['parsedNotes'] = $textRenderer->render($album->notes);

        if ($canUpload)
        {
            $this->addScript('/src/Photoalbum/js/PhotoalbumPage.js');
        }
    }

    public function drawSlider(Photoalbum $album, TemplateRenderer $templateRenderer): string
    {
        $photos = Photo::fetchAllByAlbum($album);
        return $templateRenderer->render('Photoalbum/Photoslider', ['album' => $album, 'photos' => $photos]);
    }
}
