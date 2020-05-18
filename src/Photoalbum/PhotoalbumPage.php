<?php
declare (strict_types = 1);

namespace Cyndaron\Photoalbum;

use Cyndaron\Page;
use Cyndaron\Template\Template;

class PhotoalbumPage extends Page
{
    public function __construct(Photoalbum $album, $viewMode = Photoalbum::VIEWMODE_REGULAR)
    {
        $this->model = $album;
        $this->model->load();
        parent::__construct($this->model->name);

        if ($viewMode === Photoalbum::VIEWMODE_REGULAR)
        {
            $this->addScript('/sys/js/lightbox.min.js');

            $photos = Photo::fetchAllByAlbum($this->model);
            $this->templateVars['model'] = $this->model;
            $this->templateVars['photos'] = $photos;
        }
    }

    public function drawSlider(Photoalbum $album): string
    {
        $photos = Photo::fetchAllByAlbum($album);
        return (new Template())->render('Photoalbum/Photoslider', compact('album', 'photos'));
    }
}