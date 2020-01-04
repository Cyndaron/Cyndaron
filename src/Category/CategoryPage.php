<?php
namespace Cyndaron\Category;

use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\Photoalbum\Photoalbum;
use Cyndaron\StaticPage\StaticPageModel;

class CategoryPage extends Page
{
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct() { }

    public function showCategoryIndex(Category $category)
    {
        $this->model = $category;

        parent::__construct($this->model->name);

        $subs = StaticPageModel::fetchAll(['categoryId= ?'], [$category->id], 'ORDER BY id DESC');

        $this->render([
            'type' => 'subs',
            'model' => $this->model,
            'viewMode' => $this->model->viewMode,
            'pages' => $subs,
            'tags' => $this->getTags($subs),
            'portfolioContent' => $this->getPortfolioContent(),
        ]);
    }

    /**
     * @param StaticPageModel[] $subs
     * @return array
     */
    protected function getTags(array $subs)
    {
        $tags = [];
        foreach ($subs as $sub)
        {
            $tagList = $sub->getTagList();
            if (count($tagList) > 0)
            {
                $tags += $tagList;
            }
        }
        return $tags;
    }

    protected function getPortfolioContent(): array
    {
        $portfolioContent = [];

        if ($this->model->viewMode == Category::VIEWMODE_PORTFOLIO)
        {
            $subCategories = Category::fetchAll(['categoryId = ?'], [$this->model->id]);
            foreach ($subCategories as $subCategory)
            {
                $subs = StaticPageModel::fetchAll(['categoryId = ?'], [$subCategory->id], 'ORDER BY id DESC');
                $portfolioContent[$subCategory->name] = $subs;
            }
        }

        return $portfolioContent;
    }

    public function showPhotoalbumsIndex()
    {
        parent::__construct('Fotoalbums');
        $photoalbums = Photoalbum::fetchAll(['hideFromOverview = 0'], [], 'ORDER BY id DESC');

        $this->render([
            'type' => 'photoalbums',
            'pages' => $photoalbums,
            'viewMode' => Category::VIEWMODE_TITLES
        ]);
    }

    public function showTagIndex(string $tag)
    {
        parent::__construct(ucfirst($tag));

        $tags = [];
        $pages = [];

        $subs = DBConnection::doQueryAndReturnFetchable('SELECT * FROM subs WHERE `tags` LIKE ? ORDER BY id DESC', ["%$tag%"]);
        foreach ($subs as $sub)
        {
            $sub = StaticPageModel::fromArray($sub);
            $tagList = $sub->getTagList();
            if ($tagList)
            {
                $tags += $tagList;
                if (in_array(strtolower($tag), $tagList))
                {
                    $pages[] = $sub;
                }
            }
        }

        $this->render([
            'type' => 'tag',
            'pages' => $pages,
            'tags' => $tags,
            'viewMode' => Category::VIEWMODE_BLOG,
        ]);
    }
}