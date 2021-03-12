<?php
namespace Cyndaron\Category;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\View\Page;
use Cyndaron\StaticPage\StaticPageModel;
use function ucfirst;
use function in_array;
use function strtolower;

final class TagIndexPage extends Page
{
    protected string $template = 'Category/CategoryPage';

    public function __construct(string $tag)
    {
        parent::__construct(ucfirst($tag));

        $tags = [];
        $pages = [];

        $subs = DBConnection::doQueryAndReturnFetchable('SELECT * FROM subs WHERE `tags` LIKE ? ORDER BY id DESC', ["%$tag%"]);
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
            'viewMode' => Category::VIEWMODE_BLOG,
        ]);
    }
}
