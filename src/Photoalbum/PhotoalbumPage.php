<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Cyndaron\Page\Page;
use Cyndaron\User\User;
use Cyndaron\User\UserRepository;
use Cyndaron\View\Renderer\TextRenderer;
use Cyndaron\View\Template\TemplateRenderer;

final class PhotoalbumPage
{
    public function __construct(
        private readonly PhotoalbumRepository $photoalbumRepository,
        private readonly TextRenderer $textRenderer,
        private readonly User|null $currentUser,
        private readonly UserRepository $userRepository,
        private readonly PhotoRepository $photoRepository,
    ) {

    }

    public function drawSlider(Photoalbum $album, TemplateRenderer $templateRenderer): string
    {
        $photos = $this->photoRepository->fetchAllByAlbum($album);
        return $templateRenderer->render('Photoalbum/Photoslider', ['album' => $album, 'photos' => $photos]);
    }

    public function createPage(Photoalbum $album, int $viewMode = Photoalbum::VIEWMODE_REGULAR): Page
    {
        $page = new Page();
        $page->title = $album->name;
        $page->template = 'Photoalbum/PhotoalbumPage';

        $page->model = $album;
        $page->category = $this->photoalbumRepository->getFirstLinkedCategory($album);
        $canEdit = $this->currentUser !== null && $this->userRepository->userHasRight($this->currentUser, Photoalbum::RIGHT_EDIT);
        $canUpload = $this->currentUser !== null && $this->userRepository->userHasRight($this->currentUser, Photoalbum::RIGHT_UPLOAD);

        if ($viewMode === Photoalbum::VIEWMODE_REGULAR)
        {
            $page->addScript('/js/lightbox.min.js');

            $photos = $this->photoRepository->fetchAllByAlbum($album);
            $page->templateVars['model'] = $album;
            $page->templateVars['photos'] = $photos;
            $page->templateVars['pageImage'] = $album->getImage();
        }

        $page->templateVars['canEdit'] = $canEdit;
        $page->templateVars['canUpload'] = $canUpload;
        $page->templateVars['parsedNotes'] = $this->textRenderer->render($album->notes);

        if ($canUpload)
        {
            $page->addScript('/src/Photoalbum/js/PhotoalbumPage.js');
        }

        return $page;
    }
}
