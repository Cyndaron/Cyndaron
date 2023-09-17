<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Cyndaron\Page\Page;
use Cyndaron\User\User;
use Cyndaron\View\Template\Template;

final class PhotoalbumPage extends Page
{
    public function __construct(Photoalbum $album, ?User $currentUser, int $viewMode = Photoalbum::VIEWMODE_REGULAR)
    {
        $this->model = $album;
        parent::__construct($album->name);
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

        if ($canUpload)
        {
            $this->addScript('/src/Photoalbum/js/PhotoalbumPage.js');
        }
    }

    public function drawSlider(Photoalbum $album): string
    {
        $photos = Photo::fetchAllByAlbum($album);
        return (new Template())->render('Photoalbum/Photoslider', ['album' => $album, 'photos' => $photos]);
    }
}
