<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Cyndaron\Page;
use Cyndaron\Template\Template;

class PhotoalbumPage extends Page
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
    }

    public function drawSlider(Photoalbum $album): string
    {
        $photos = Photo::fetchAllByAlbum($album);
        return (new Template())->render('Photoalbum/Photoslider', ['album' => $album, 'photos' => $photos]);
    }
}
