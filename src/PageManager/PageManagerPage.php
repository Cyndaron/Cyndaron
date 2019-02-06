<?php
declare(strict_types = 1);

namespace Cyndaron\PageManager;

use Cyndaron\DBConnection;
use Cyndaron\Pagina;
use Cyndaron\Request;
use Cyndaron\User\User;use Cyndaron\Widget\Knop;
use Cyndaron\Widget\PageTabs;

require_once __DIR__ . '/../../check.php';

class PageManagerPage extends Pagina
{
    public function __construct()
    {
        $this->voegScriptToe('/src/PageManager/PageManagerPage.js');
        parent::__construct('Paginaoverzicht');
        $this->toonPrepagina();
        $connectie = DBConnection::getPDO();
        $this->connectie = $connectie;

        $currentPage = Request::getVar(1) ?? 'sub';

        echo new PageTabs([
            'sub' => 'Statische pagina\'s',
            'category' => 'Categorieën',
            'photoalbum' => 'Fotoalbums',
            'friendlyurl' => 'Friendly URL\'s',
        ], '/pagemanager/', $currentPage);

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

        echo new Knop('new', '/editor-statischepagina', 'Nieuwe statische pagina', 'Nieuwe statische pagina');
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
            ?>
            <h3 class="text-italic"><?=$categorie?></h3>
            <table class="table table-striped table-bordered pm-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Naam</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                foreach ($subs as $id => $name):

                    $vvsub = $this->connectie->prepare('SELECT * FROM vorigesubs WHERE id= ?');
                    $vvsub->execute([$id]);
                    $hasLastVersion = boolval($vvsub->fetchColumn());
                    $name = strtr($name, [' ' => '&nbsp;']);
                    ?>
                    <tr id="pm-row-sub-<?=$id?>">
                        <td><?=$id?></td>
                        <td>
                            <span style="font-size: 15px;">
                                <a href="/toonsub.php?id=<?=$id?>"><b><?=$name?></b></a>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <?= new Knop('edit', '/editor-statischepagina?id=' . $id, 'Bewerk deze statische pagina', null, 16);?>
                                <button class="btn btn-outline-cyndaron btn-sm pm-delete" data-type="sub" data-id="<?=$id;?>" ><span class="glyphicon glyphicon-trash"></span></button>
                                <button class="btn btn-outline-cyndaron btn-sm pm-addtomenu" data-type="sub" data-id="<?=$id;?>" ><span class="glyphicon glyphicon-bookmark"></span></button>
                                <?php if ($hasLastVersion)
                                {
                                    echo new Knop('lastversion', '/editor-statischepagina?vorigeversie=1&amp;id=' . $id, 'Vorige versie terugzetten', null, 16);
                                }
                                ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table><?php
        }
    }

    public function showCategories()
    {
        ?>
        <h2>Categorieën</h2>

        <div class="form-inline">
            <label for="pm-category-new-name" class="mb-2 mr-sm-2">Nieuwe categorie:</label>
            <input class="form-control mb-2 mr-sm-2" id="pm-category-new-name" type="text"/>
            <button id="pm-create-category" data-csrf-token="<?=User::getCSRFToken('category', 'add')?>" class="btn btn-success mb-2 "><span class="glyphicon glyphicon-plus"></span> Aanmaken</button>
        </div>

        <table class="table table-striped table-bordered pm-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Naam</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $categories = $this->connectie->prepare('SELECT id,naam FROM categorieen ORDER BY id ASC;');
            $categories->execute();
            foreach ($categories as $category): ?>
                <tr id="pm-row-category-<?=$category['id']?>">
                    <td><?=$category['id']?></td>
                    <td>
                        <a href="/tooncategorie.php?id=<?php echo $category['id']; ?>"><b><?php echo $category['naam']; ?></b></a>
                    </td>
                    <td>
                        <div class="btn-group"><?php
                            echo new Knop('edit', '/editor-categorie?id=' . $category['id'], 'Deze categorie bewerken', null, 16);?>
                            <button class="btn btn-outline-cyndaron btn-sm pm-delete" data-type="category" data-id="<?=$category['id'];?>" ><span class="glyphicon glyphicon-trash" title="Verwijder deze categorie"></span></button>
                            <button class="btn btn-outline-cyndaron btn-sm pm-addtomenu" data-type="category" data-id="<?=$category['id'];?>" ><span class="glyphicon glyphicon-bookmark" title="Voeg deze categorie toe aan het menu"></span></button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    public function showPhotoAlbums()
    {
        ?>
        <h2>Fotoalbums</h2>

        <div class="form-inline">
            <label for="pm-photoalbum-new-name" class="mb-2 mr-sm-2">Nieuw fotoalbum:</label>
            <input class="form-control mb-2 mr-sm-2" id="pm-photoalbum-new-name" type="text"/>
            <button id="pm-create-photoalbum" class="btn btn-success mb-2 "><span class="glyphicon glyphicon-plus"></span> Aanmaken</button>
        </div>

        <table class="table table-striped table-bordered pm-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Naam</th>
                <th>Acties</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $photoalbums = $this->connectie->prepare('SELECT id,naam FROM fotoboeken ORDER BY id ASC;');
            $photoalbums->execute();
            foreach ($photoalbums as $photoalbum): ?>
                <tr id="pm-row-photoalbum-<?=$photoalbum['id']?>">
                    <td><?=$photoalbum['id']?></td>
                    <td>
                        <a href="/toonfotoboek.php?id=<?php echo $photoalbum['id']; ?>"><b><?php echo $photoalbum['naam']; ?></b></a>
                    </td>
                    <td>
                        <div class="btn-group"><?php
                            echo new Knop('edit', '/editor-fotoalbum?id=' . $photoalbum['id'], 'Bewerk dit fotoalbum', null, 16); ?>
                            <button class="btn btn-outline-cyndaron btn-sm pm-delete" data-type="photoalbum" data-id="<?=$photoalbum['id'];?>" ><span class="glyphicon glyphicon-trash" title="Verwijder dit fotoalbum"></span></button>
                            <button class="btn btn-outline-cyndaron btn-sm pm-addtomenu" data-type="photoalbum" data-id="<?=$photoalbum['id'];?>" ><span class="glyphicon glyphicon-bookmark" title="Voeg dit fotoalbum toe aan het menu"></span></button>
                        </div
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table><br/>
        <?php
    }

    public function showFriendlyURLs()
    {
        ?>
        <h2>Friendly URL's</h2>

            <table class="table table-striped table-bordered pm-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>URL</th>
                        <th>Verwijzingsdoel</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                <tr>
                    <td></td>
                    <td>
                        Nieuwe friendly URL:
                        <input id="pm-friendlyurl-new-name" type="text" placeholder="URL"
                               class="form-control form-control-inline"/></td>
                    <td>
                        <input id="pm-friendlyurl-new-target" type="text" placeholder="Verwijzingsdoel"
                               class="form-control form-control-inline"/>
                    </td>
                    <td>
                        <button id="pm-create-friendlyurl" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span>
                            Aanmaken
                        </button>
                    </td>
                </tr>
                <?php
                $friendlyurls = $this->connectie->prepare('SELECT * FROM friendlyurls ORDER BY naam ASC;');
                $friendlyurls->execute();
                $counter = 1;

                foreach ($friendlyurls as $friendlyurl): ?>
                    <tr id="pm-row-friendlyurl-<?=$friendlyurl['naam'];?>">
                        <td><?=$counter++;?></td>
                        <td>
                            <strong><?=$friendlyurl['naam'];?></strong>
                        </td>
                        <td>
                            <?=$friendlyurl['doel']?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-outline-cyndaron btn-sm pm-delete" data-type="friendlyurl" data-id="<?=$friendlyurl['naam'];?>" ><span class="glyphicon glyphicon-trash" title="Verwijder deze friendly URL"></span></button>
                                <button class="btn btn-outline-cyndaron btn-sm pm-addtomenu" data-type="friendlyurl" data-id="<?=$friendlyurl['naam'];?>" ><span class="glyphicon glyphicon-bookmark" title="Voeg deze friendly URL toe aan het menu"></span></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach;?>
                </tbody>
            </table>
        </form>
        <?php
    }
}