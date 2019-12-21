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
        $this->templateVars['type'] = 'subs';
        $this->model = $category;

        $controls = sprintf('<a href="/editor/category/%d" class="btn btn-outline-cyndaron" title="Deze categorie bewerken" role="button"><span class="glyphicon glyphicon-pencil"></span></a>', $category->id);
        parent::__construct($this->model->name);
        $this->setTitleButtons($controls);
        $this->showPrePage();

        $this->templateVars['model'] = $this->model;

        $tags = [];
        $subs = StaticPageModel::fetchAll(['categoryId= ?'], [$category->id], 'ORDER BY id DESC');
        foreach ($subs as $sub)
        {
            $tagList = $sub->getTagList();
            if (count($tagList) > 0)
            {
                $tags += $tagList;
            }
        }
        $this->templateVars['pages'] = $subs;
        $this->templateVars['viewMode'] = $this->model->viewMode;
        $this->templateVars['tags'] = $tags;

        if ($this->model->viewMode == Category::VIEWMODE_PORTFOLIO)
        {
            $portfolioContent = [];
            $subCategories = Category::fetchAll(['categoryId = ?'], [$category->id]);
            foreach ($subCategories as $subCategory)
            {
                $subs = StaticPageModel::fetchAll(['categoryId = ?'], [$subCategory->id], 'ORDER BY id DESC');
                $portfolioContent[$subCategory->name] = $subs;
            }
            $this->templateVars['portfolioContent'] = $portfolioContent;
        }

        $this->showPostPage();
    }

    public function showPhotoalbumsIndex()
    {
        parent::__construct('Fotoalbums');
        $this->showPrePage();
        $photoalbums = Photoalbum::fetchAll(['hideFromOverview = 0'], [], 'ORDER BY id DESC');
        $this->templateVars['type'] = 'photoalbums';
        $this->templateVars['pages'] = $photoalbums;
        $this->templateVars['viewMode'] = Category::VIEWMODE_TITLES;

        $this->showPostPage();
    }

    public function showTagIndex(string $tag)
    {
        $this->templateVars['type'] = 'tag';
        parent::__construct(ucfirst($tag));
        $this->showPrePage();

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
        $this->templateVars['pages'] = $pages;
        $this->templateVars['tags'] = $tags;
        $this->templateVars['viewMode'] = Category::VIEWMODE_BLOG;

        $this->showPostPage();
    }
}