<?php /** @noinspection PhpIncludeInspection */

namespace Cyndaron;

use Cyndaron\Category\Category;
use Cyndaron\User\User;

/*
 * Copyright © 2009-2017, Michael Steenbeek
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */


class Page
{
    protected $extraMeta = '';
    protected $title = '';
    protected $titleButtons = null;
    protected $extraScripts = [];
    protected $websiteName = '';
    protected $body = '';
    /** @var Model $model */
    protected $model = null;

    public function __construct(string $title, string $body = '')
    {
        $this->title = $title;
        $this->body = $body;
    }

    public function setExtraMeta(string $extraMeta)
    {
        $this->extraMeta = $extraMeta;
    }

    public function setTitleButtons(string $titleButtons)
    {
        $this->titleButtons = $titleButtons;
    }

    public function showPrePage()
    {
        $this->websiteName = Setting::get('websitenaam');
        $titel = $this->title . ' - ' . $this->websiteName;

        ?>
        <!DOCTYPE HTML>
        <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="twitter:card" content="summary" />
            <meta name="twitter:title" content="<?=$titel;?>" />
            <meta name="twitter:description" content="Klik hier om verder te lezen..." />
            <title><?=$titel;?></title>
            <?php
            printf('<link href="/contrib/Bootstrap/css/bootstrap.min.css?r=%s" type="text/css" rel="stylesheet" />', CyndaronInfo::ENGINE_VERSION);
            printf('<link href="/contrib/Glyphicons/css/glyphicons.min.css?r=%s" type="text/css" rel="stylesheet" />', CyndaronInfo::ENGINE_VERSION);
            printf('<link href="/sys/css/lightbox.min.css?r=%s" type="text/css" rel="stylesheet" />', CyndaronInfo::ENGINE_VERSION);
            printf('<link href="/sys/css/cyndaron.min.css?r=%s" type="text/css" rel="stylesheet" />', CyndaronInfo::ENGINE_VERSION);
            printf('<link href="/user.css?r=%s" type="text/css" rel="stylesheet" />', CyndaronInfo::ENGINE_VERSION);
            if ($favicon = Setting::get('favicon'))
            {
                $extensie = substr(strrchr($favicon, "."), 1);
                echo '<link rel="icon" type="image/' . $extensie . '" href="' . $favicon . '">';
            }
            ?>
            <style type="text/css">
                <?php
                static::showIfSet(Setting::get('achtergrondkleur'), 'body.cyndaron, .lightboxOverlay { background-color: ',";}\n");
                static::showIfSet(Setting::get('menukleur'), '.menu { background-color: ',";}\n");
                static::showIfSet(Setting::get('menuachtergrond'), '.menu { background-image: url(\'',"');}\n");
                static::showIfSet(Setting::get('artikelkleur'), '.inhoud { background-color: ',";}\n");
                $accentColor = Setting::get('accentColor');
                if ($accentColor): ?>
                a { color: <?=$accentColor?> }
                .btn-primary { background-color: <?=$accentColor?>; border-color: <?=$accentColor?> ;}
                .dropdown-item.active, .dropdown-item:active { background-color: <?=$accentColor?>; }

                <?php endif; ?>
            </style>
            <?php
            if (file_exists(__DIR__ . '/../extra-head.php'))
            {
                include __DIR__ . '/../extra-head.php';
            }
            ?>
        </head>
        <body class="cyndaron" data-artikelkleur="<?=Setting::get('artikelkleur');?>"><?php
        if (file_exists(__DIR__ . '/../extra-body-start.php'))
        {
            include __DIR__ . '/../extra-body-start.php';
        }

        echo '
        <div class="paginacontainer">
        <header class="menucontainer">';

        $this->showMenu();

        echo '</header>';

        if ($this->isFrontPage() && Setting::get('voorpagina_is_jumbo') && Setting::get('jumbo_inhoud'))
        {
            echo '<div class="welkom-jumbo">';
            echo Setting::get('jumbo_inhoud');
            echo '</div>';
        }

        echo '<main class="inhoudcontainer"><article class="inhoud">';

        $class = '';
        if ($this->isFrontPage())
        {
            $class = 'voorpagina';
        }

        $title = $this->generateBreadcrumbs();

        echo '<div class="paginatitel ' . $class . '"><h1 style="display: inline; margin-right:8px;">' . $title . '</h1>';
        static::showIfSetAndAdmin($this->titleButtons, '<div class="btn-group" style="vertical-align: bottom; margin-bottom: 3px;">', '</div>');
        echo "</div>\n";
    }

    public function isFrontPage(): bool
    {
        if ($_SERVER['REQUEST_URI'] === '/')
        {
            return true;
        }
        return false;
    }

    protected function showMenu()
    {
        $websitelogo = Setting::get('websitelogo');
        $inverseClass = (Setting::get('menuthema') == 'donker') ? 'navbar-dark' : 'navbar-light';
        $navbar = $websitelogo ? sprintf('<img alt="" src="%s"> ', $websitelogo) : $this->websiteName;
        ?>
        <nav class="menu navbar navbar-expand-md <?= $inverseClass; ?>">
            <a class="navbar-brand" href="/"><?= $navbar; ?></a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">

                    <?php
                    $menuarray = $this->getMenu();

                    if (count($menuarray) > 0)
                    {
                        foreach ($menuarray as $menuitem)
                        {
                            if (strpos($menuitem['link'], '/category/') === 0 && $menuitem['isDropdown'])
                            {
                                $this->printCategoryDropdown($menuitem);
                            }
                            else
                            {
                                if ($this->menuItemIsCurrentPage($menuitem['link']))
                                {
                                    echo '<li class="nav-item active">';
                                }
                                else
                                {
                                    echo '<li class="nav-item">';
                                }

                                if ($menuitem['isImage'])
                                {
                                    printf('<a class="nav-link img-in-menuitem" href="%1$s"><img src="%2$s" alt="%1$s"/></a></li>', $menuitem['link'], $menuitem['naam']);
                                }
                                else
                                {
                                    echo '<a class="nav-link" href="' . $menuitem['link'] . '">' . $menuitem['naam'] . '</a></li>';
                                }
                            }
                        }
                    }
                    ?>

                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <?php
                    if (User::isLoggedIn())
                    {
                        if (User::isAdmin())
                        {
                            ?>
                            <li class="nav-item">
                                <a class="nav-link" title="Nieuwe statische pagina aanmaken" href="/editor/sub"><span
                                            class="glyphicon glyphicon-plus"></span></a>
                            </li>
                            <?php

                            $this->printMenuDropdown('<span class="glyphicon glyphicon-wrench"></span>', [
                                ['link' => '/system', 'title' => '<span class="glyphicon glyphicon-cog"></span>&nbsp; Systeembeheer'],
                                ['link' => '/pagemanager', 'title' => '<span class="glyphicon glyphicon-th-list"></span>&nbsp; Pagina-overzicht'],
                                ['link' => '/menu-editor', 'title' => '<span class="glyphicon glyphicon-menu-hamburger"></span>&nbsp; Menu bewerken'],
                                ['link' => '/user/manager', 'title' => '<span class="glyphicon glyphicon-user"></span>&nbsp; Gebruikersbeheer'],
                            ]);
                        }

                        $this->printMenuDropdown('<span class="glyphicon glyphicon-user"></span>', [
                            ['link' => '', 'title' => $_SESSION['naam']],
                            ['link' => '/user/logout', 'title' => '<span class="glyphicon glyphicon-log-out"></span> Uitloggen']
                        ]);
                    }
                    else
                    {
                        ?>
                        <li class="nav-item">
                            <a class="nav-link" title="Inloggen" href="/user/login"><span class="glyphicon glyphicon-lock"></span></a>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
        </nav>

        <?php
        $meldingen = User::getNotifications();
        if ($meldingen)
        {
            echo '<div class="meldingencontainer">';
            echo '<div class="meldingen alert alert-info"><ul>';

            foreach ($meldingen as $melding)
            {
                echo '<li>' . $melding . '</li>';
            }

            echo '</ul></div></div>';
        }
    }

    private function menuItemIsCurrentPage(string $menuItem): bool
    {
        // Vergelijking na || betekent testen of de hoofdurl is opgevraagd
        if ($menuItem == basename(substr($_SERVER['REQUEST_URI'], 1)) || ($menuItem == '/' && $_SERVER['REQUEST_URI'] === '/'))
        {
            return true;
        }

        return false;
    }

    public function showPostPage()
    {
        // article: inhoud. main: inhoudcontainer. div: paginacontainer.
        ?>
        </article></main></div>

        <div id="confirm-dangerous" class="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Weet u het zeker?</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Sluiten">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                    </div>
                    <div class="modal-footer">
                        <button id="confirm-dangerous-no" type="button" class="btn btn-outline-cyndaron" data-dismiss="modal">Annuleren</button>
                        <button id="confirm-dangerous-yes" type="button" class="btn btn-danger">Verwijderen</button>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript" src="/contrib/jQuery/jquery-3.3.1.min.js"></script>
        <script type="text/javascript" src="/contrib/Bootstrap/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="/sys/js/cyndaron.js"></script>
    <?php
    foreach ($this->extraScripts as $extraScript)
    {
        printf('<script type="text/javascript" src="%s"></script>', $extraScript);
    }
    if (file_exists(__DIR__ . '/../extra-body-end.php'))
    {
        include __DIR__ . '/../extra-body-end.php';
    }
    ?>

        </body>
        </html>
        <?php
    }

    public function addScript($script)
    {
        $this->extraScripts[] = $script;
    }

    public function getMenu(): array
    {
        if (!User::hasSufficientReadLevel())
        {
            return [];
        }
        $menu = DBConnection::doQueryAndFetchAll('SELECT * FROM menu ORDER BY priority, id;');
        $menuitems = [];
        $frontPage = Setting::get('frontPage');

        foreach ($menu as $menuitem)
        {
            $url = new Url($menuitem['link']);

            if ($menuitem['alias'])
            {
                $menuitem['naam'] = strtr($menuitem['alias'], [' ' => '&nbsp;']);
            }
            else
            {
                $menuitem['naam'] = $url->getPageTitle();
            }

            if ($menuitem['link'] == $frontPage)
            {
                $menuitem['link'] = '/';
            }
            // For dropdowns, this is not necessary and it makes detection harder down the line.
            elseif (!$menuitem['isDropdown'])
            {
                $menuitem['link'] = $url->getFriendly();
            }
            $menuitems[] = $menuitem;
        }
        return $menuitems;
    }

    public static function showIfSet($string, $before = '', $after = '')
    {
        if ($string)
        {
            echo $before;
            echo $string;
            echo $after;
        }
    }

    public static function showIfSetAndAdmin($string, $before = '', $after = '')
    {
        if (User::isAdmin() && $string)
        {
            echo $before;
            echo $string;
            echo $after;
        }
    }

    public static function showIfSetAndNotAdmin($string, $before = '', $after = '')
    {
        if (!User::isAdmin() && $string)
        {
            echo $before;
            echo $string;
            echo $after;
        }
    }

    protected function printCategoryDropdown(array $menuitem)
    {
        $id = intval(str_replace('/category/', '', $menuitem['link']));
        $pagesInCategory = DBConnection::doQueryAndFetchAll("
            SELECT * FROM
            (
                SELECT 'sub' AS type, id, name FROM subs WHERE categoryId=?
                UNION
                SELECT 'photoalbum' AS type, id, name FROM photoalbums WHERE categoryId=?
                UNION
                SELECT 'category' AS type, id, name FROM categories WHERE categoryId=?
            ) AS one
            ORDER BY name ASC;",
            [$id, $id, $id]);

        $items = [];
        foreach ($pagesInCategory as $pagina)
        {
            $url = new Url(sprintf('/%s/%d', $pagina['type'], $pagina['id']));
            $link = $url->getFriendly();
            $items[] = ['link' => $link, 'title' => $pagina['name']];
        }

        $this->printMenuDropdown($menuitem['naam'], $items);
    }

    protected function printMenuDropdown(string $title, array $items)
    {
        ?>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?=$title;?>
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                <?php foreach($items as $item): ?>
                    <?php if (!empty($item['link'])): ?>
                        <a class="dropdown-item" href="<?=$item['link'];?>"><?=$item['title'];?></a>
                    <?php else: ?>
                        <span class="dropdown-item"><i><?=$item['title'];?></i></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </li>
        <?php
    }

    public function showBody(): void
    {
        echo $this->body;
    }

    protected function generateBreadcrumbs(): string
    {
        $title = '';
        $titleParts = [$this->title];
        if ($this->model !== null && $this->model::HAS_CATEGORY)
        {
            $titleParts = [];
            if ($this->model->showBreadcrumbs)
            {
                if ($this->model->categoryId)
                {
                    /** @var Category $category */
                    $category = Category::loadFromDatabase((int)$this->model->categoryId);
                    $titleParts[] = $category->name;
                }
            }
            $titleParts[] = $this->model->name;
        }

        $count = count($titleParts);
        if ($count === 1)
        {
            $title = $titleParts[0];
        }
        else
        {
            for ($i = 0; $i < $count; $i++)
            {
                $class = ($i === 0 && $count > 1) ? 'breadcrumb-main-item' : 'breadcrumb-item';
                $title .= sprintf('<span class="%s">%s</span>', $class, $titleParts[$i]);
                if ($i !== $count - 1)
                    $title .= '<span class="breadcrumb-separator"> // </span>';
            }
        }

        return $title;
    }
}