<?php
namespace Cyndaron\Category;

use Cyndaron\Page\Page;
use Cyndaron\Photoalbum\Photoalbum;
use Cyndaron\Photoalbum\PhotoalbumRepository;
use Cyndaron\Url\UrlService;

final class PhotoalbumIndexPage extends Page
{
    public string $template = 'Category/CategoryPage';

    public function __construct(UrlService $urlService, PhotoalbumRepository $photoalbumRepository)
    {
        $this->title = 'Fotoalbums';
        $photoalbums = $photoalbumRepository->fetchAll(['hideFromOverview = 0'], [], 'ORDER BY id DESC');

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
