<?php
namespace Cyndaron\Category;

use Cyndaron\View\Page;
use Cyndaron\Photoalbum\Photoalbum;

final class PhotoalbumIndexPage extends Page
{
    protected string $template = 'Category/CategoryPage';

    public function __construct()
    {
        parent::__construct('Fotoalbums');
        $photoalbums = Photoalbum::fetchAll(['hideFromOverview = 0'], [], 'ORDER BY id DESC');

        $this->addTemplateVars([
            'type' => 'photoalbums',
            'model' => null,
            'pages' => $photoalbums,
            'tags' => [],
            'viewMode' => Category::VIEWMODE_TITLES
        ]);
    }
}
