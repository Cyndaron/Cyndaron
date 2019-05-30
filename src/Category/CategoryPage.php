<?php
namespace Cyndaron\Category;

use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\Url;
use Cyndaron\Util;

class CategoryPage extends Page
{
    public function __construct($id)
    {
        if ($id != 'fotoboeken')
        {
            $this->showCategoryIndex(intval($id));
        }
        else
        {
            $this->showPhotoalbumsIndex();
        }
    }

    private function showCategoryIndex(int $id)
    {
        if ($id < 0)
        {
            header("Location: /error/404");
            die('Incorrecte parameter ontvangen.');
        }

        $this->model = new Category($id);
        $this->model->load();

        $controls = sprintf('<a href="/editor/category/%d" class="btn btn-outline-cyndaron" title="Deze categorie bewerken" role="button"><span class="glyphicon glyphicon-pencil"></span></a>', $id);
        parent::__construct($this->model->name);
        $this->setTitleButtons($controls);
        $this->showPrePage();

        echo $this->model->description;

        $onlyShowTitles = (bool)$this->model->onlyShowTitles;
        if ($onlyShowTitles)
        {
            echo '<ul class="zonderbullets">';
        }

        $paginas = DBConnection::doQueryAndFetchAll('SELECT * FROM subs WHERE categoryId= ? ORDER BY id DESC', [$id]);
        foreach ($paginas as $pagina)
        {
            $url = new Url('/sub/' . $pagina['id']);
            $link = $url->getFriendly();
            if ($onlyShowTitles)
            {
                echo '<li><h3><a href="' . $link . '">' . $pagina['name'] . '</a></h3></li>';
            }
            else
            {
                echo "\n<p><h3><a href=\"" . $link . '">' . $pagina['name'] . "</a></h3>\n";
                echo Util::wordlimit(trim($pagina['text']), 30, "...") . '<a href="' . $link . '"><br /><i>Meer lezen...</i></a></p>';
            }
        }
        if ($onlyShowTitles)
        {
            echo '</ul>';
        }

        $this->showPostPage();
    }

    private function showPhotoalbumsIndex()
    {
        parent::__construct('Fotoalbums');
        $this->showPrePage();
        $fotoboeken = DBConnection::doQueryAndFetchAll('SELECT * FROM photoalbums ORDER BY id DESC');

        echo '<ul class="zonderbullets">';
        foreach ($fotoboeken as $fotoboek)
        {
            $url = new Url('/photoalbum/' . $fotoboek['id']);
            $link = $url->getFriendly();
            echo '<li><h3><a href="' . $link . '">' . $fotoboek['name'] . '</a></h3></li>';
        }
        echo '</ul>';

        $this->showPostPage();
    }
}