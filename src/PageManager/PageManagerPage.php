<?php
declare (strict_types = 1);

namespace Cyndaron\PageManager;

use Cyndaron\DBConnection;
use Cyndaron\Mailform\Mailform;
use Cyndaron\Page;
use Cyndaron\User\User;
use Cyndaron\Widget\Button;
use Cyndaron\Widget\Toolbar;

class PageManagerPage extends Page
{
    private static array $pageTypes = [];
    
    public function __construct($currentPage)
    {
        $this->addScript('/src/PageManager/PageManagerPage.js');
        parent::__construct('Paginaoverzicht');

        $pageTabs = [];
        foreach (static::$pageTypes as $pageType => $data)
        {
            $pageTabs[$pageType] = $data['name'];
        }

        ob_start();
        $function = static::$pageTypes[$currentPage]['tabDraw'];
        $tabContents = $function();
        if (!$tabContents)
            $tabContents = ob_get_contents();
        ob_end_clean();

        $this->render([
            'pageTabs' => $pageTabs,
            'currentPage' => $currentPage,
            'tabContents' => $tabContents,
        ]);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private static function showSubs()
    {
        echo new Toolbar('', '', (string)new Button('new', '/editor/sub', 'Nieuwe statische pagina', 'Nieuwe statische pagina'));

        /** @noinspection SqlResolve */
        $subs = DBConnection::doQueryAndFetchAll('SELECT id, name, "Zonder categorie" AS category FROM subs WHERE categoryId NOT IN (SELECT id FROM categories) UNION (SELECT s.id AS id, s.name AS name, c.name AS category FROM subs AS s,categories AS c WHERE s.categoryId=c.id ORDER BY category, name, id ASC);');
        $subsPerCategory = [];

        foreach ($subs as $sub)
        {
            if (empty($subsPerCategory[$sub['category']]))
            {
                $subsPerCategory[$sub['category']] = [];
            }

            $subsPerCategory[$sub['category']][$sub['id']] = $sub['name'];
        }

        foreach ($subsPerCategory as $category => $subs)
        {
            ?>
            <h3 class="text-italic"><?=$category?></h3>
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

                    $vvsub = DBConnection::doQueryAndFetchFirstRow('SELECT * FROM sub_backups WHERE id= ?', [$id]);
                    $hasLastVersion = !empty($vvsub);
                    $name = strtr($name, [' ' => '&nbsp;']);
                    ?>
                    <tr id="pm-row-sub-<?=$id?>">
                        <td><?=$id?></td>
                        <td>
                            <span style="font-size: 15px;">
                                <a href="/sub/<?=$id?>"><b><?=$name?></b></a>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <?= new Button('edit', '/editor/sub/' . $id, 'Bewerk deze statische pagina', null, 16);?>
                                <button class="btn btn-outline-cyndaron btn-sm pm-delete" data-type="sub" data-id="<?=$id;?>" data-csrf-token="<?=User::getCSRFToken('sub', 'delete')?>"><span class="glyphicon glyphicon-trash"></span></button>
                                <button class="btn btn-outline-cyndaron btn-sm pm-addtomenu" data-type="sub" data-id="<?=$id;?>" data-csrf-token="<?=User::getCSRFToken('sub', 'addtomenu')?>"><span class="glyphicon glyphicon-bookmark"></span></button>
                                <?php if ($hasLastVersion)
                                {
                                    echo new Button('lastversion', "/editor/sub/$id/previous", 'Vorige versie terugzetten', null, 16);
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

    /** @noinspection PhpUnused */
    public static function showCategories()
    {
        echo new Toolbar('', '', '
            <label for="pm-category-new-name" class="mr-sm-2">Nieuwe categorie:</label>
            <input class="form-control mr-sm-2" id="pm-category-new-name" type="text"/>
            <button type="button" id="pm-create-category" data-csrf-token="' . User::getCSRFToken('category', 'add') . '" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span> Aanmaken</button>
        ');
        ?>
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
            $categories = DBConnection::doQueryAndFetchAll('SELECT id,name FROM categories ORDER BY id ASC;');
            foreach ($categories as $category): ?>
                <tr id="pm-row-category-<?=$category['id']?>">
                    <td><?=$category['id']?></td>
                    <td>
                        <a href="/category/<?php echo $category['id']; ?>"><b><?php echo $category['name']; ?></b></a>
                    </td>
                    <td>
                        <div class="btn-group"><?php
                            echo new Button('edit', '/editor/category/' . $category['id'], 'Deze categorie bewerken', null, 16);?>
                            <button class="btn btn-outline-cyndaron btn-sm pm-delete" data-type="category" data-id="<?=$category['id'];?>" data-csrf-token="<?=User::getCSRFToken('category', 'delete')?>"><span class="glyphicon glyphicon-trash" title="Verwijder deze categorie"></span></button>
                            <button class="btn btn-outline-cyndaron btn-sm pm-addtomenu" data-type="category" data-id="<?=$category['id'];?>" data-csrf-token="<?=User::getCSRFToken('category', 'addtomenu')?>"><span class="glyphicon glyphicon-bookmark" title="Voeg deze categorie toe aan het menu"></span></button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    /** @noinspection PhpUnused */
    public static function showPhotoAlbums()
    {
        echo new Toolbar('', '', '
            <label for="pm-photoalbum-new-name" class="mr-sm-2">Nieuw fotoalbum:</label>
            <input class="form-control mr-sm-2" id="pm-photoalbum-new-name" type="text"/>
            <button type="button" id="pm-create-photoalbum" data-csrf-token="' . User::getCSRFToken('photoalbum', 'add') . '" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span> Aanmaken</button>
        ');
        ?>
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
            $photoalbums = DBConnection::doQueryAndFetchAll('SELECT id,name FROM photoalbums ORDER BY id ASC;');
            foreach ($photoalbums as $photoalbum): ?>
                <tr id="pm-row-photoalbum-<?=$photoalbum['id']?>">
                    <td><?=$photoalbum['id']?></td>
                    <td>
                        <a href="/photoalbum/<?php echo $photoalbum['id']; ?>"><b><?php echo $photoalbum['name']; ?></b></a>
                    </td>
                    <td>
                        <div class="btn-group"><?php
                            echo new Button('edit', '/editor/photoalbum/' . $photoalbum['id'], 'Bewerk dit fotoalbum', null, 16); ?>
                            <button class="btn btn-outline-cyndaron btn-sm pm-delete" data-type="photoalbum" data-id="<?=$photoalbum['id'];?>" data-csrf-token="<?=User::getCSRFToken('photoalbum', 'delete')?>"><span class="glyphicon glyphicon-trash" title="Verwijder dit fotoalbum"></span></button>
                            <button class="btn btn-outline-cyndaron btn-sm pm-addtomenu" data-type="photoalbum" data-id="<?=$photoalbum['id'];?>" data-csrf-token="<?=User::getCSRFToken('photoalbum', 'addtomenu')?>"><span class="glyphicon glyphicon-bookmark" title="Voeg dit fotoalbum toe aan het menu"></span></button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * @noinspection PhpUnused
     */
    public static function showFriendlyURLs()
    {
        echo new Toolbar('', '', '
            <label for="pm-friendlyurl-new-name" class="mr-sm-2">Nieuwe friendly URL:</label> 
            <input id="pm-friendlyurl-new-name" type="text" placeholder="URL" class="form-control mr-sm-2" required/>
            <input id="pm-friendlyurl-new-target" type="text" placeholder="Verwijzingsdoel" class="form-control mr-sm-2" required/>
            <button id="pm-create-friendlyurl" type="button" data-csrf-token="' . User::getCSRFToken('friendlyurl', 'add') . '" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span> Aanmaken</button>
        ');
        ?>
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
                <?php
                $friendlyurls = DBConnection::doQueryAndFetchAll('SELECT * FROM friendlyurls ORDER BY name ASC;');
                $counter = 1;

                foreach ($friendlyurls as $friendlyurl): ?>
                    <tr id="pm-row-friendlyurl-<?=$friendlyurl['name'];?>">
                        <td><?=$counter++;?></td>
                        <td>
                            <strong><?=$friendlyurl['name'];?></strong>
                        </td>
                        <td>
                            <?=$friendlyurl['target']?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-outline-cyndaron btn-sm pm-delete" data-type="friendlyurl" data-id="<?=$friendlyurl['name'];?>" data-csrf-token="<?=User::getCSRFToken('friendlyurl', 'delete')?>"><span class="glyphicon glyphicon-trash" title="Verwijder deze friendly URL"></span></button>
                                <button class="btn btn-outline-cyndaron btn-sm pm-addtomenu" data-type="friendlyurl" data-id="<?=$friendlyurl['name'];?>" data-csrf-token="<?=User::getCSRFToken('friendlyurl', 'addtomenu')?>"><span class="glyphicon glyphicon-bookmark" title="Voeg deze friendly URL toe aan het menu"></span></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach;?>
                </tbody>
            </table>
        <?php
    }

    /**
     * @noinspection PhpUnused
     */
    public static function showMailforms()
    {
        echo new Toolbar('', '', (string)new Button('new', '/editor/mailform', 'Nieuw mailformulier', 'Nieuw mailformulier'));

        $mailforms = Mailform::fetchAll();
        ?>

        <table class="table table-striped table-bordered pm-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Naam</th>
                <th>Acties</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($mailforms as $mailform):?>
                <tr>
                    <td><?=$mailform->id?></td>
                    <td>
                        <?=$mailform->name?>
                    </td>
                    <td>
                        <div class="btn-group">
                            <a class="btn btn-outline-cyndaron btn-sm" href="/editor/mailform/<?=$mailform->id?>"><span class="glyphicon glyphicon-pencil" title="Bewerk dit mailformulier"></span></a>
                            <button class="btn btn-danger btn-sm pm-delete" data-type="mailform" data-id="<?=$mailform->id;?>" data-csrf-token="<?=User::getCSRFToken('mailform', 'delete')?>"><span class="glyphicon glyphicon-trash" title="Verwijder dit mailformulier"></span></button>
                        </div>

                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Adds a tab definition to the page manager.
     *
     * @param array $pageType
     */
    public static function addPageType(array $pageType)
    {
        static::$pageTypes = array_merge(static::$pageTypes, $pageType);
    }
}