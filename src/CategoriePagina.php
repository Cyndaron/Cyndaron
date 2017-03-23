<?php
namespace Cyndaron;


class CategoriePagina extends Pagina
{
    public function __construct()
    {
        $this->connectie = DBConnection::getPDO();
        $id = Request::geefGetVeilig('id');

        if ($id != 'fotoboeken')
        {
            $this->toonCategorieIndex(intval($id));
        }
        else
        {
            $this->toonFotoalbumsIndex();
        }
    }

    private function toonCategorieIndex(int $id)
    {
        if ($id < 0)
        {
            header("Location: 404.php");
            die('Incorrecte parameter ontvangen.');
        }

        $naam = DBConnection::geefEen("SELECT naam FROM categorieen WHERE id= ?;", [$id]);
        $alleentitel = DBConnection::geefEen("SELECT alleentitel FROM categorieen WHERE id=?", [$id]);
        $controls = sprintf('<a href="editor-categorie?id=%d" class="btn btn-default" title="Deze categorie bewerken" role="button"><span class="glyphicon glyphicon-pencil"></span></a>', $id);

        parent::__construct($naam);
        $this->maakTitelknoppen($controls);
        $this->toonPrePagina();

        $beschrijving = DBConnection::geefEen('SELECT beschrijving FROM categorieen WHERE id= ?', [$id]);
        echo $beschrijving;
        $paginas = $this->connectie->prepare('SELECT * FROM subs WHERE categorieid= ? ORDER BY id DESC');
        $paginas->execute([$id]);

        if ($alleentitel)
        {
            echo '<ul class="zonderbullets">';
        }

        foreach ($paginas->fetchAll() as $pagina)
        {
            $url = new Url('toonsub.php?id=' . $pagina['id']);
            $link = $url->geefFriendly();
            if ($alleentitel)
            {
                echo '<li><h3><a href="' . $link . '">' . $pagina['naam'] . '</a></h3></li>';
            }
            else
            {
                echo "\n<p><h3><a href=\"" . $link . '">' . $pagina['naam'] . "</a></h3>\n";
                echo Util::woordlimiet(trim($pagina['tekst']), 30, "...") . '<a href="' . $link . '"><br /><i>Meer lezen...</i></a></p>';
            }
        }
        if ($alleentitel)
        {
            echo '</ul>';
        }

        $this->toonPostPagina();
    }

    private function toonFotoalbumsIndex()
    {
        parent::__construct('Fotoboeken');
        $this->toonPrePagina();
        $fotoboeken = $this->connectie->prepare('SELECT * FROM fotoboeken ORDER BY id DESC');
        $fotoboeken->execute([]);

        echo '<ul class="zonderbullets">';
        foreach ($fotoboeken->fetchAll() as $fotoboek)
        {
            $url = new Url('toonfotoboek.php?id=' . $fotoboek['id']);
            $link = $url->geefFriendly();
            echo '<li><h3><a href="' . $link . '">' . $fotoboek['naam'] . '</a></h3></li>';
        }
        echo '</ul>';

        $this->toonPostPagina();
    }
}