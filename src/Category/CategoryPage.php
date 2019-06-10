<?php
namespace Cyndaron\Category;

use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\Url;
use Cyndaron\Util;

class CategoryPage extends Page
{
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct($id)
    {
        if ($id === '0' || $id == 'fotoboeken')
        {
            $this->twigVars['type'] = 'photoalbums';
            $this->showPhotoalbumsIndex();
        }
        elseif ($id == 'tag')
        {
            $this->twigVars['type'] = 'tag';
            $this->showTagIndex(Request::getVar(2));
        }
        else
        {
            if ($id < 0)
            {
                header("Location: /error/404");
                die('Incorrecte parameter ontvangen.');
            }
            $this->twigVars['type'] = 'subs';
            $this->showCategoryIndex(intval($id));
        }
    }

    private function showCategoryIndex(int $id)
    {
        $this->model = new Category($id);
        $this->model->load();

        $controls = sprintf('<a href="/editor/category/%d" class="btn btn-outline-cyndaron" title="Deze categorie bewerken" role="button"><span class="glyphicon glyphicon-pencil"></span></a>', $id);
        parent::__construct($this->model->name);
        $this->setTitleButtons($controls);
        $this->showPrePage();

        $this->twigVars['model'] = $this->model;

        $tags = [];
        $pages = DBConnection::doQueryAndFetchAll('SELECT * FROM subs WHERE categoryId= ? ORDER BY id DESC', [$id]);
        foreach ($pages as &$page)
        {
            $url = new Url('/sub/' . $page['id']);
            $page['link'] = $url->getFriendly();
            $page['blurb'] = html_entity_decode(Util::wordlimit(trim($page['text']), 30));

            preg_match("/<img.*?src=\"(.*?)\".*?>/si", $page['text'], $match);
            $page['image'] = $match[1] ?? '';
            if ($page['tags'])
            {
                $tags += explode(';', strtolower($page['tags']));
            }
        }
        $this->twigVars['pages'] = $pages;
        $this->twigVars['viewMode'] = $this->model->viewMode;
        $this->twigVars['tags'] = $tags;

        $this->showPostPage();
    }

    private function showPhotoalbumsIndex()
    {
        parent::__construct('Fotoalbums');
        $this->showPrePage();
        $photoalbums = DBConnection::doQueryAndFetchAll('SELECT * FROM photoalbums ORDER BY id DESC');

        foreach ($photoalbums as &$photoalbum)
        {
            $url = new Url('/photoalbum/' . $photoalbum['id']);
            $photoalbum['link'] = $url->getFriendly();
        }

        $this->twigVars['pages'] = $photoalbums;
        $this->twigVars['viewMode'] = 1;

        $this->showPostPage();
    }

    private function showTagIndex($tag)
    {
        parent::__construct(ucfirst($tag));
        $this->showPrePage();

        $tags = [];
        $pages = [];
        $subs = DBConnection::doQueryAndFetchAll('SELECT * FROM subs ORDER BY id DESC');
        foreach ($subs as $page)
        {
            if ($page['tags'])
            {
                $tagsArr = explode(';', strtolower($page['tags']));
                $tags += $tagsArr;
                if (in_array(strtolower($tag), $tagsArr))
                {
                    $url = new Url('/sub/' . $page['id']);
                    $page['link'] = $url->getFriendly();
                    $page['blurb'] = html_entity_decode(Util::wordlimit(trim($page['text']), 30));
                    preg_match("/<img.*?src=\"(.*?)\".*?>/si", $page['text'], $match);
                    $page['image'] = $match[1] ?? '';
                    $pages[] = $page;
                }
            }
        }
        $this->twigVars['pages'] = $pages;
        $this->twigVars['tags'] = $tags;
        $this->twigVars['viewMode'] = 2;

        $this->showPostPage();
    }
}