<?php
namespace Cyndaron;

use Cyndaron\Widget\Knop;

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


class Pagina
{
    protected $extraMeta = "";
    protected $paginanaam = "";
    protected $titelknoppen = null;
    protected $connectie = null;
    protected $nietDelen = false;
    protected $extraScripts = [];
    protected $websitenaam = '';

    public function __construct($paginanaam)
    {
        if ($this->connectie == null)
        {
            $this->connectie = DBConnection::getPDO();
        }

        $this->paginanaam = $paginanaam;
    }

    public function maakExtraMeta(string $extraMeta)
    {
        $this->extraMeta = $extraMeta;
    }

    public function maaknietDelen(bool $bool)
    {
        $this->nietDelen = $bool;
    }

    public function maakTitelknoppen(string $titelknoppen)
    {
        $this->titelknoppen = $titelknoppen;
    }

    public function toonPrepagina()
    {
        $this->websitenaam = Setting::get('websitenaam');
        $titel = $this->paginanaam . ' - ' . $this->websitenaam;

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
            printf('<link href="/vendor/Bootstrap/css/bootstrap.min.css?r=%s" type="text/css" rel="stylesheet" />', CyndaronInfo::ENGINE_VERSIE);
            printf('<link href="/vendor/Glyphicons/css/glyphicons.min.css?r=%s" type="text/css" rel="stylesheet" />', CyndaronInfo::ENGINE_VERSIE);
            printf('<link href="/sys/css/lightbox.min.css?r=%s" type="text/css" rel="stylesheet" />', CyndaronInfo::ENGINE_VERSIE);
            printf('<link href="/sys/css/cyndaron.min.css?r=%s" type="text/css" rel="stylesheet" />', CyndaronInfo::ENGINE_VERSIE);
            printf('<link href="/user.css?r=%s" type="text/css" rel="stylesheet" />', CyndaronInfo::ENGINE_VERSIE);
            if ($favicon = Setting::get('favicon'))
            {
                $extensie = substr(strrchr($favicon, "."), 1);
                echo '<link rel="icon" type="image/' . $extensie . '" href="' . $favicon . '">';
            }
            ?>
            <style type="text/css">
                <?php
                static::toonIndienAanwezig(Setting::get('achtergrondkleur'), 'body.cyndaron, .lightboxOverlay { background-color: ',";}\n");
                static::toonIndienAanwezig(Setting::get('menukleur'), '.menu { background-color: ',";}\n");
                static::toonIndienAanwezig(Setting::get('menuachtergrond'), '.menu { background-image: url(\'',"');}\n");
                static::toonIndienAanwezig(Setting::get('artikelkleur'), '.inhoud { background-color: ',";}\n");
                ?>
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

        if ($this->nietDelen == false)
        {
            if (Setting::get('facebook_share') == 1)
            {
                echo '<div id="fb-root"></div>
                <script type="text/javascript" src="sys/js/facebook-like.js"></script>';
            }
        }

        echo '
        <div class="paginacontainer">
        <div class="menucontainer">';

        $this->toonMenu();

        echo '</div>';

        if ($this->isVoorPagina() && Setting::get('voorpagina_is_jumbo') && Setting::get('jumbo_inhoud'))
        {
            echo '<div class="welkom-jumbo">';
            echo Setting::get('jumbo_inhoud');
            echo '</div>';
        }

        echo '<div class="inhoudcontainer"><div class="inhoud">';

        $class = '';
        if ($this->isVoorPagina())
        {
            $class = 'voorpagina';
        }

        echo '<div class="paginatitel ' . $class . '"><h1 style="display: inline; margin-right:8px;">' . $this->paginanaam . '</h1>';
        static::toonIndienAanwezigEnAdmin($this->titelknoppen, '<div class="btn-group" style="vertical-align: bottom; margin-bottom: 3px;">', '</div>');
        echo "</div>\n";
    }

    public function isVoorPagina(): bool
    {
        if (substr($_SERVER['REQUEST_URI'], -1) == '/')
        {
            return true;
        }
        return false;
    }

    protected function toonMenu()
    {
        $websitelogo = Setting::get('websitelogo');
        $inverseClass = (Setting::get('menuthema') == 'donker') ? 'navbar-dark' : 'navbar-light';
        $navbar = $websitelogo ? sprintf('<img alt="" src="%s"> ', $websitelogo) : $this->websitenaam;
        ?>
        <nav class="menu navbar navbar-expand-md <?= $inverseClass; ?>">
            <a class="navbar-brand" href="./"><?= $navbar; ?></a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">

                    <?php
                    $menuarray = $this->geefMenu();

                    if (count($menuarray) > 0)
                    {
                        foreach ($menuarray as $menuitem)
                        {
                            if (strpos($menuitem['link'], 'tooncategorie') !== false && $menuitem['isDropdown'])
                            {
                                $this->printCategoryDropdown($menuitem);
                            }
                            else
                            {
                                if ($this->menuItemIsHuidigePagina($menuitem['link']))
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
                                <a class="nav-link" title="Nieuwe statische pagina aanmaken" href="editor-statischepagina"><span
                                            class="glyphicon glyphicon-plus"></span></a>
                            </li>
                            <?php

                            $this->printMenuDropdown('<span class="glyphicon glyphicon-wrench"></span>', [
                                ['link' => 'configuratie', 'title' => '<span class="glyphicon glyphicon-cog"></span>&nbsp; Configuratie'],
                                ['link' => 'pagemanager', 'title' => '<span class="glyphicon glyphicon-th-list"></span>&nbsp; Pagina-overzicht'],
                                ['link' => 'menu-editor', 'title' => '<span class="glyphicon glyphicon-menu-hamburger"></span>&nbsp; Menu bewerken'],
                                ['link' => 'reset-wachtwoord', 'title' => '<span class="glyphicon glyphicon-repeat"></span>&nbsp; Wachtwoorden bewerken'],
                            ]);
                        }

                        $this->printMenuDropdown('<span class="glyphicon glyphicon-user"></span>', [
                            ['link' => '', 'title' => $_SESSION['naam']],
                            ['link' => 'logoff', 'title' => '<span class="glyphicon glyphicon-log-out"></span> Uitloggen']
                        ]);
                    }
                    else
                    {
                        ?>
                        <li class="nav-item">
                            <a class="nav-link" title="Inloggen" href="login"><span class="glyphicon glyphicon-lock"></span></a>
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

    private function menuItemIsHuidigePagina(string $menuItem): bool
    {
        // Vergelijking na || betekent testen of de hoofdurl is opgevraagd
        if ($menuItem == basename(substr($_SERVER['REQUEST_URI'], 1)) || ($menuItem == './' && substr($_SERVER['REQUEST_URI'], -1) == '/'))
        {
            return true;
        }

        return false;
    }

    public function toonPostPagina()
    {
        $this->toonDeelknoppen();

        // Eerste div: inhoud. Tweede div: inhoudcontainer. Derde div: paginacontainer
        ?>
        </div></div></div>

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

        <script type="text/javascript" src="/vendor/jQuery/jquery-3.3.1.min.js"></script>
        <script type="text/javascript" src="/vendor/Bootstrap/js/bootstrap.min.js"></script>
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

    public function voegScriptToe($scriptnaam)
    {
        $this->extraScripts[] = $scriptnaam;
    }

    private function toonDeelknoppen()
    {
        if ($this->nietDelen == false && Setting::get('facebook_share') == 1)
        {
            echo '<br /><div class="fb-like" data-href="//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '" data-send="false" data-layout="button_count" data-width="450" data-show-faces="true" data-font="trebuchet ms"></div>';
        }
    }

    public function geefMenu(): array
    {
        if (!User::hasSufficientReadLevel())
        {
            return [];
        }
        $menu = $this->connectie->prepare('SELECT * FROM menu ORDER BY volgorde ASC;');
        $menu->execute();
        $menuitems = [];
        $eersteitem = true;

        foreach ($menu->fetchAll() as $menuitem)
        {
            $url = new Url($menuitem['link']);

            if ($menuitem['alias'])
            {
                $menuitem['naam'] = strtr($menuitem['alias'], [' ' => '&nbsp;']);
            }
            else
            {
                $menuitem['naam'] = $url->geefPaginanaam();
            }

            if ($eersteitem)
            {
                // De . is nodig omdat het menu anders niet goed werkt in subdirectories.
                $menuitem['link'] = './';
            }
            else
            {
                // For dropdowns, this is not necessary and it makes detection harder down the line.
                if (!$menuitem['isDropdown'])
                {
                    $menuitem['link'] = $url->geefFriendly();
                }
            }
            $menuitems[] = $menuitem;
            $eersteitem = false;
        }
        return $menuitems;
    }

    public static function toonIndienAanwezig($string, $voor = null, $na = null)
    {
        if ($string)
        {
            echo $voor;
            echo $string;
            echo $na;
        }
    }

    public static function toonIndienAanwezigEnAdmin($string, $voor = null, $na = null)
    {
        if (User::isAdmin() && $string)
        {
            echo $voor;
            echo $string;
            echo $na;
        }
    }

    public static function toonIndienAanwezigEnGeenAdmin($string, $voor = null, $na = null)
    {
        if (!User::isAdmin() && $string)
        {
            echo $voor;
            echo $string;
            echo $na;
        }
    }

    protected function printCategoryDropdown(array $menuitem)
    {
        $id = intval(str_replace('tooncategorie.php?id=', '', $menuitem['link']));
        $pagesInCategory = $this->connectie->prepare("
            SELECT * FROM
            (
                SELECT 'sub' AS type, id, naam FROM subs WHERE categorieid=?
                UNION
                SELECT 'fotoboek' AS type, id, naam FROM fotoboeken WHERE categorieid=?
                UNION
                SELECT 'categorie' AS type, id, naam FROM categorieen WHERE categorieid=?
            ) AS een
            ORDER BY naam ASC;");
        $pagesInCategory->execute([$id, $id, $id]);

        $items = [];
        foreach ($pagesInCategory->fetchAll() as $pagina)
        {
            $url = new Url(sprintf('toon%s.php?id=%d', $pagina['type'], $pagina['id']));
            $link = $url->geefFriendly();
            $items[] = ['link' => $link, 'title' => $pagina['naam']];
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
}