<?php
namespace Cyndaron\Category;

use Cyndaron\Page;
use Cyndaron\Photoalbum\Photoalbum;

class PhotoalbumIndexPage extends Page
{
    protected string $template = 'Category/CategoryPage';

    public function __construct()
    {
        parent::__construct('Fotoalbums');
        $photoalbums = Photoalbum::fetchAll(['hideFromOverview = 0'], [], 'ORDER BY id DESC');

        $this->addTemplateVars([
            'type' => 'photoalbums',
            'pages' => $photoalbums,
            'viewMode' => Category::VIEWMODE_TITLES
        ]);
    }
}