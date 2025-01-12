<?php
namespace Cyndaron\Category;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Page\Page;
use Cyndaron\StaticPage\StaticPageModel;
use Cyndaron\Url\UrlService;
use function in_array;
use function strtolower;
use function ucfirst;

final class TagIndexPage extends Page
{
    public string $template = 'Category/CategoryPage';

    public function __construct(UrlService $urlService, string $tag)
    {
        $this->title = ucfirst($tag);

        $tags = [];
        $pages = [];

        $subs = DBConnection::getPDO()->doQueryAndFetchAll('SELECT * FROM subs WHERE `tags` LIKE ? ORDER BY id DESC', ["%$tag%"]);
        foreach ($subs as $sub)
        {
            $sub = StaticPageModel::fromArray($sub);
            $tagList = $sub->getTagList();
            if ($tagList !== [])
            {
                $tags += $tagList;
                if (in_array(strtolower($tag), $tagList, true))
                {
                    $pages[] = $sub;
                }
            }
        }

        $this->addTemplateVars([
            'type' => 'tag',
            'pages' => $pages,
            'tags' => $tags,
            'viewMode' => ViewMode::Blog,
            'urlService' => $urlService,
        ]);
    }
}
