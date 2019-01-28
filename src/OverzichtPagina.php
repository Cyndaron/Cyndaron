<?php
namespace Cyndaron;

use Cyndaron\Menu\MenuModel;
use Cyndaron\Widget\Knop;
use Cyndaron\Widget\PageTabs;

require_once __DIR__ . '/../check.php';

class OverzichtPagina extends Pagina
{
    public function __construct()
    {
        $type = Request::getVar(1);
        $action = Request::getVar(2);
        $id = intval(Request::getVar(3));
        $name = Request::getVar(3);
        $confirm = Request::getVar(4);

        if ($type == 'categorie')
        {
            if ($action == 'new')
            {
                CategorieModel::nieuweCategorie($name);
            }
            elseif ($action == 'edit')
            {
                CategorieModel::wijzigCategorie($id, $name);
            }
            elseif ($action == 'delete' && $confirm == 1)
            {
                CategorieModel::verwijderCategorie($id);
            }
            elseif ($action == 'addtomenu')
            {
                MenuModel::voegToeAanMenu('tooncategorie.php?id=' . $id);
            }
        }
        elseif ($type == 'photoalbum')
        {
            if ($action == 'new')
            {
                FotoalbumModel::nieuwFotoalbum($name);
            }
            elseif ($action == 'bewerken')
            {
                FotoalbumModel::wijzigFotoalbum($id, $name);
            }
            elseif ($action == 'delete' && $confirm == 1)
            {
                FotoalbumModel::verwijderFotoalbum($id);
            }
            elseif ($action == 'addtomenu')
            {
                MenuModel::voegToeAanMenu('toonfotoboek.php?id=' . $id);
            }
        }
        elseif ($type == 'sub')
        {
            if ($action == 'delete' && $confirm == 1)
            {
                $model = new StatischePaginaModel($id);
                $model->verwijder();
            }
            elseif ($action == 'addtomenu')
            {
                MenuModel::voegToeAanMenu('toonsub.php?id=' . $id);
            }

        }
        elseif ($type == 'friendlyurl')
        {
            if ($action == 'new')
            {
                $doel = new Url(Request::geefPostVeilig('doel'));
                $doel->maakFriendly($name);
            }
            elseif ($action == 'delete' && $confirm == 1)
            {
                $name = Request::geefGetVeilig('naam');
                Url::verwijderFriendlyUrl($name);
            }
            elseif ($action == 'addtomenu')
            {
                $name = Request::geefGetVeilig('naam');
                MenuModel::voegToeAanMenu($name);
            }
        }

        parent::__construct('Paginaoverzicht');
        $this->maakNietDelen(true);
        $this->toonPrepagina();
        $connectie = DBConnection::getPDO();
        $this->connectie = $connectie;

        if ($action == 'delete' && $confirm != 1)
        {
            $url = $_SERVER['REQUEST_URI'] . '/1';
            echo '  <form method="post" action="' . $url . '">
        <p>Weet u zeker dat u dit item wilt verwijderen?
        <input name="inhoud" value="1" style="display:none;"/>
        </p><p>
        <input type="submit" class="btn btn-primary" value="Ja"/>
        <a role="button" class="btn btn-outline-cyndaron">Nee</a>
        </p></form>';
        }

        $currentPage = Request::getVar(1);

        echo new PageTabs([
            'sub' => 'Statische pagina\'s',
            'category' => 'Categorieën',
            'photoalbum' => 'Fotoalbums',
            'friendlyurl' => 'Friendly URL\'s',
        ], '/allpages/', $currentPage);

        echo '<div class="container-fluid tab-contents">';

        switch ($currentPage)
        {
            case 'category':
                $this->showCategories();
                break;
            case 'friendlyurl':
                $this->showFriendlyURLs();
                break;
            case 'photoalbum':
                $this->showPhotoAlbums();
                break;
            case 'sub':
            default:
                $this->showSubs();
        }

        echo '<div>';

        $this->toonPostPagina();
    }

    private function showSubs()
    {
        echo '<h2>Statische pagina\'s</h2>';

        echo new Knop('new', 'editor-statischepagina', 'Nieuwe statische pagina', 'Nieuwe statische pagina');
        echo '<br />';

        $subs = $this->connectie->prepare('SELECT id, naam, "Zonder categorie" AS categorie FROM subs WHERE categorieid NOT IN (SELECT id FROM categorieen) UNION (SELECT s.id AS id, s.naam AS naam, c.naam AS categorie FROM subs AS s,categorieen AS c WHERE s.categorieid=c.id ORDER BY categorie, naam, id ASC);');
        $subs->execute();
        $subsPerCategorie = [];

        foreach ($subs->fetchAll() as $sub)
        {
            if (empty($subsPerCategorie[$sub['categorie']]))
            {
                $subsPerCategorie[$sub['categorie']] = [];
            }

            $subsPerCategorie[$sub['categorie']][$sub['id']] = $sub['naam'];
        }

        foreach ($subsPerCategorie as $categorie => $subs)
        {
            echo '<h3 class="text-italic">' . $categorie . '</h3>';
            echo '<table class="table table-striped table-bordered table-overzicht">';

            foreach ($subs as $subId => $subNaam)
            {
                echo '<tr><td><div class="btn-group">';
                echo new Knop('edit', 'editor-statischepagina?id=' . $subId, 'Bewerk deze statische pagina', null, 16);
                echo new Knop('delete', '/allpages/sub/delete/' . $subId, 'Verwijder deze statische pagina', null, 16);
                echo new Knop('addtomenu', '/allpages/sub/addtomenu/' . $subId, 'Voeg deze statische pagina toe aan het menu', null, 16);
                $vvsub = $this->connectie->prepare('SELECT * FROM vorigesubs WHERE id= ?');
                $vvsub->execute([$subId]);

                if ($vvsub->fetchColumn())
                {
                    echo new Knop('lastversion', 'editor-statischepagina?vorigeversie=1&amp;id=' . $subId, 'Vorige versie terugzetten', null, 16);
                }
                echo '</div></td><td>';
                $subNaam = strtr($subNaam, [' ' => '&nbsp;']);
                echo '<span style="font-size: 15px;">';
                echo '<a href="toonsub.php?id=' . $subId . '"><b>' . $subNaam . '</b></a>';
                echo "</span></td></tr>\n";
            }

            echo '</table>';
        }
    }

    public function showCategories()
    {
        ?>
        <!-- Categorieën -->
        <h2>Categorieën</h2>
        <form method="post" action="/allpages/category/new" class="form-inline">
            <div class="form-group">
                <label for="naam">Nieuwe categorie:</label> <input class="form-control" id="naam" name="naam"
                                                                   type="text"/>
            </div>
            <button type="submit" class="btn btn-outline-cyndaron"><span class="glyphicon glyphicon-plus"></span> Aanmaken
            </button>
        </form>
        <br/>
        <table class="table table-striped table-bordered table-overzicht"><?php
            $categories = $this->connectie->prepare('SELECT id,naam FROM categorieen ORDER BY id ASC;');
            $categories->execute();
            foreach ($categories as $category)
            {
                ?>
                <tr>
                    <td>
                        <div class="btn-group"><?php
                            echo new Knop('edit', 'editor-categorie?id=' . $category['id'], 'Deze categorie bewerken', null, 16);
                            echo new Knop('delete', '/allpages/category/delete/' . $category['id'], 'Verwijder deze categorie', null, 16);
                            echo new Knop('addtomenu', '/allpages/category/addtomenu/' . $category['id'], 'Voeg deze categorie toe aan het menu', null, 16); ?>
                        </div>
                    </td>
                    <td>
                        <a href="tooncategorie.php?id=<?php echo $category['id']; ?>"><b><?php echo $category['naam']; ?></b></a>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <?php
    }

    public function showPhotoAlbums()
    {
        ?>
        <!-- Fotoboeken -->
        <h2>Fotoboeken</h2>
        <form method="post" action="/allpages/photoalbum/new" class="form-inline">
            <div class="form-group">
                <label for="fobonaam">Nieuw fotoboek:</label> <input id="fobonaam" name="naam" type="text"
                                                                     class="form-control"/>
                <button type="submit" class="btn btn-outline-cyndaron"><span class="glyphicon glyphicon-plus"></span> Aanmaken
                </button>
            </div>
        </form>
        <br/>
        <table class="table table-striped table-bordered table-overzicht"><?php
            $photoalbums = $this->connectie->prepare('SELECT id,naam FROM fotoboeken ORDER BY id ASC;');
            $photoalbums->execute();
            foreach ($photoalbums as $photoalbum)
            {
                ?>
                <tr>
                    <td>
                        <div class="btn-group"><?php
                            echo new Knop('edit', 'editor-fotoalbum?id=' . $photoalbum['id'], 'Bewerk dit fotoboek', null, 16);
                            echo new Knop('delete', '/allpages/photoalbum/delete/' . $photoalbum['id'], 'Verwijder dit fotoboek', null, 16);
                            echo new Knop('addtomenu', '/allpages/photoalbum/addtomenu/' . $photoalbum['id'], 'Voeg dit fotoboek toe aan het menu', null, 16); ?>
                        </div
                    </td>
                    <td>
                        <a href="toonfotoboek.php?id=<?php echo $photoalbum['id']; ?>"><b><?php echo $photoalbum['naam']; ?></b></a>
                        (mapnummer <?php echo $photoalbum['id']; ?>)
                    </td>
                </tr>
            <?php } ?>
        </table><br/>
        <?php
    }

    public function showFriendlyURLs()
    {
        ?>
        <h2>Friendly URL's</h2>
        <br/>

        <form method="post" action="/allpages/friendlyurl/new" class="form-inline">

            <table class="table table-striped table-bordered table-overzicht">
                <tr>
                    <th></th>
                    <th>URL</th>
                    <th>Verwijzingsdoel</th>
                </tr>
                <tr>
                    <td>Nieuwe friendly URL:</td>
                    <td>
                        <input id="furl-naam" name="naam" type="text" placeholder="URL"
                               class="form-control form-control-inline"/></td>
                    <td>
                        <input id="furl-doel" name="doel" type="text" placeholder="Verwijzingsdoel"
                               class="form-control form-control-inline"/>
                        <button class="btn btn-outline-cyndaron" type="submit"><span class="glyphicon glyphicon-plus"></span>
                            Aanmaken
                        </button>
                    </td>
                </tr>
                <?php
                $friendlyurls = $this->connectie->prepare('SELECT naam,doel FROM friendlyurls ORDER BY naam ASC;');
                $friendlyurls->execute();

                foreach ($friendlyurls as $friendlyurl)
                {
                    echo '<tr><td><div class="btn-group">';
                    echo new Knop('delete', '/allpages/friendlyurl/delete/' . $friendlyurl['naam'], 'Verwijder deze friendly URL', null, 16);
                    echo new Knop('addtomenu', '/allpages/friendlyurl/addtomenu/' . $friendlyurl['naam'], 'Voeg deze friendly url toe aan het menu', null, 16);
                    echo '</div></td><td><strong>' . $friendlyurl['naam'] . '</strong></td><td>' . $friendlyurl['doel'] . '</td></tr>';
                }
                ?>
            </table>
        </form>
        <?php
    }
}