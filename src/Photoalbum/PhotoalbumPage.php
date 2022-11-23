<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Cyndaron\User\User;
use Cyndaron\View\Page;
use Cyndaron\View\Template\Template;

final class PhotoalbumPage extends Page
{
    public function __construct(Photoalbum $album, int $viewMode = Photoalbum::VIEWMODE_REGULAR)
    {
        $this->model = $album;
        parent::__construct($album->name);

        if ($viewMode === Photoalbum::VIEWMODE_REGULAR)
        {
            $this->addScript('/js/lightbox.min.js');

            $photos = Photo::fetchAllByAlbum($album);
            $this->templateVars['model'] = $album;
            $this->templateVars['photos'] = $photos;
            $this->templateVars['pageImage'] = $album->getImage();
        }

        if (User::isAdmin())
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
