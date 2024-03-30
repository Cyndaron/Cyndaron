<?php
namespace Cyndaron\Category;

use Cyndaron\Page\Page;
use Cyndaron\Photoalbum\Photoalbum;
use Cyndaron\Url\UrlService;

final class PhotoalbumIndexPage extends Page
{
    protected string $template = 'Category/CategoryPage';

    public function __construct(UrlService $urlService)
    {
        parent::__construct('Fotoalbums');
        $photoalbums = Photoalbum::fetchAll(['hideFromOverview = 0'], [], 'ORDER BY id DESC');

        $this->addTemplateVars([
            'type' => 'photoalbums',
            'model' => null,
            'pages' => $photoalbums,
            'tags' => [],
            'viewMode' => ViewMode::Titles,
            'urlService' => $urlService,
        ]);
    }
}
