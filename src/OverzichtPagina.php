<?php
namespace Cyndaron;

use Cyndaron\Widget\Knop;

require_once __DIR__ . '/../check.php';

class OverzichtPagina extends Pagina
{
    public function __construct()
    {
        $type = Request::geefGetVeilig('type');
        $actie = Request::geefGetVeilig('actie');
        $id = Request::geefGetVeilig('id');
        $zeker = Request::geefGetVeilig('zeker');
        $naam = Request::geefPostVeilig('naam');

        if ($type == 'categorie')
        {
            if ($actie == 'nieuw')
            {
                CategorieModel::nieuweCategorie($naam);
            }
            elseif ($actie == 'bewerken')
            {
                CategorieModel::wijzigCategorie($id, $naam);
            }
            elseif ($actie == 'verwijderen' && $zeker == 1)
            {
                CategorieModel::verwijderCategorie($id);
            }
            elseif ($actie == 'aanmenutoevoegen')
            {
                MenuModel::voegToeAanMenu('tooncategorie.php?id=' . $id);
            }
        }
        elseif ($type == 'fotoboek')
        {
            if ($actie == 'nieuw')
            {
                FotoalbumModel::nieuwFotoalbum($naam);
            }
            elseif ($actie == 'bewerken')
            {
                FotoalbumModel::wijzigFotoalbum($id, $naam);
            }
            elseif ($actie == 'verwijderen' && $zeker == 1)
            {
                FotoalbumModel::verwijderFotoalbum($id);
            }
            elseif ($actie == 'aanmenutoevoegen')
            {
                MenuModel::voegToeAanMenu('toonfotoboek.php?id=' . $id);
            }
        }
        elseif ($type == 'sub')
        {
            if ($actie == 'verwijderen' && $zeker == 1)
            {
                $model = new StatischePaginaModel($id);
                $model->verwijder();
            }
            elseif ($actie == 'aanmenutoevoegen')
            {
                MenuModel::voegToeAanMenu('toonsub.php?id=' . $id);
            }

        }
        elseif ($type == 'friendlyurl')
        {
            if ($actie == 'nieuw')
            {
                $doel = new Url(Request::geefPostVeilig('doel'));
                $doel->maakFriendly($naam);
            }
            elseif ($actie == 'verwijderen' && $zeker == 1)
            {
                $naam = Request::geefGetVeilig('naam');
                Url::verwijderFriendlyUrl($naam);
            }
            elseif ($actie == 'aanmenutoevoegen')
            {
                $naam = Request::geefGetVeilig('naam');
                MenuModel::voegToeAanMenu($naam);
            }
        }

        parent::__construct('Paginaoverzicht');
        $this->maakNietDelen(true);
        $this->voegScriptToe('sys/js/pagina-overzicht.js');
        $this->toonPrepagina();
        $connectie = DBConnection::getPDO();

        if ($actie == 'verwijderen' && $zeker != 1)
        {
            $url = $_SERVER['REQUEST_URI'] . '&amp;zeker=1';
            echo '  <form method="post" action="' . $url . '">
        <p>Weet u zeker dat u dit item wilt verwijderen?
        <input name="inhoud" value="1" style="display:none;"/>
        </p><p>
        <input type="submit" style="width:200px" value="Ja"/>
        <button type="button" style="width:200px" onclick="window.location=\'overzicht.php\'"/>Nee</button>
        </p></form>';
        }

        /* Subs */
        echo '<h2>Statische pagina\'s</h2>';

        echo new Knop('nieuw', 'editor-statischepagina', 'Nieuwe statische pagina', 'Nieuwe statische pagina');
        echo '<br />';

        $subs = $connectie->prepare('SELECT id, naam, "Zonder categorie" AS categorie FROM subs WHERE categorieid NOT IN (SELECT id FROM categorieen) UNION (SELECT s.id AS id, s.naam AS naam, c.naam AS categorie FROM subs AS s,categorieen AS c WHERE s.categorieid=c.id ORDER BY categorie, naam, id ASC);');
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
                echo new Knop('bewerken', 'editor-statischepagina?id=' . $subId, 'Bewerk deze sub', null, 16);
                echo new Knop('verwijderen', 'overzicht.php?type=sub&amp;actie=verwijderen&amp;id=' . $subId, 'Verwijder deze sub', null, 16);
                echo new Knop('aanmenutoevoegen', 'overzicht.php?type=sub&amp;actie=aanmenutoevoegen&amp;id=' . $subId, 'Voeg deze sub toe aan het menu', null, 16);
                $vvsub = $connectie->prepare('SELECT * FROM vorigesubs WHERE id= ?');
                $vvsub->execute([$subId]);

                if ($vvsub->fetchColumn())
                {
                    echo new Knop('vorigeversie', 'editor-statischepagina?vorigeversie=1&amp;id=' . $subId, 'Vorige versie terugzetten', null, 16);
                }
                echo '</div></td><td>';
                $subNaam = strtr($subNaam, [' ' => '&nbsp;']);
                echo '<span style="font-size: 15px;">';
                echo '<a href="toonsub.php?id=' . $subId . '"><b>' . $subNaam . '</b></a>';
                echo "</span></td></tr>\n";
            }

            echo '</table>';
        }
        ?>

        <!-- Categorieën -->
        <h2>Categorieën</h2>
        <form method="post" action="overzicht.php?type=categorie&amp;actie=nieuw" class="form-inline">
            <div class="form-group">
                <label for="naam">Nieuwe categorie:</label> <input class="form-control" id="naam" name="naam"
                                                                   type="text"/>
            </div>
            <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-plus"></span> Aanmaken
            </button>
        </form>
        <br/>
        <table class="table table-striped table-bordered table-overzicht"><?php
            $categorieen = $connectie->prepare('SELECT id,naam FROM categorieen ORDER BY id ASC;');
            $categorieen->execute();
            foreach ($categorieen as $categorie)
            {
                ?>
                <tr>
                    <td>
                        <div class="btn-group"><?php
                            echo new Knop('bewerken', 'editor-categorie?id=' . $categorie['id'], 'Deze categorie bewerken', null, 16);
                            echo new Knop('verwijderen', 'overzicht.php?type=categorie&amp;actie=verwijderen&amp;id=' . $categorie['id'], 'Verwijder deze categorie', null, 16);
                            echo new Knop('aanmenutoevoegen', 'overzicht.php?type=categorie&amp;actie=aanmenutoevoegen&amp;id=' . $categorie['id'], 'Voeg deze categorie toe aan het menu', null, 16); ?>
                        </div>
                    </td>
                    <td>
                        <a href="tooncategorie.php?id=<?php echo $categorie['id']; ?>"><b><?php echo $categorie['naam']; ?></b></a>
                    </td>
                </tr>
            <?php } ?>
        </table>

        <!-- Fotoboeken -->
        <h2>Fotoboeken</h2>
        <form method="post" action="overzicht.php?type=fotoboek&amp;actie=nieuw" class="form-inline">
            <div class="form-group">
                <label for="fobonaam">Nieuw fotoboek:</label> <input id="fobonaam" name="naam" type="text"
                                                                     class="form-control"/>
                <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-plus"></span> Aanmaken
                </button>
            </div>
        </form>
        <br/>
        <table class="table table-striped table-bordered table-overzicht"><?php
            $fotoboeken = $connectie->prepare('SELECT id,naam FROM fotoboeken ORDER BY id ASC;');
            $fotoboeken->execute();
            foreach ($fotoboeken as $fotoboek)
            {
                ?>
                <tr>
                    <td>
                        <div class="btn-group"><?php
                            echo new Knop('bewerken', 'editor-fotoalbum?id=' . $fotoboek['id'], 'Bewerk dit fotoboek', null, 16);
                            echo new Knop('verwijderen', 'overzicht.php?type=fotoboek&amp;actie=verwijderen&amp;id=' . $fotoboek['id'], 'Verwijder dit fotoboek', null, 16);
                            echo new Knop('aanmenutoevoegen', 'overzicht.php?type=fotoboek&amp;actie=aanmenutoevoegen&amp;id=' . $fotoboek['id'], 'Voeg dit fotoboek toe aan het menu', null, 16); ?>
                        </div
                    </td>
                    <td>
                        <a href="toonfotoboek.php?id=<?php echo $fotoboek['id']; ?>"><b><?php echo $fotoboek['naam']; ?></b></a>
                        (mapnummer <?php echo $fotoboek['id']; ?>)
                    </td>
                </tr>
            <?php } ?>
        </table><br/>
        <h2>Friendly URL's</h2>
        <br/>

        <form method="post" action="overzicht.php?type=friendlyurl&amp;actie=nieuw" class="form-inline">

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
                        <button class="btn btn-default" type="submit"><span class="glyphicon glyphicon-plus"></span>
                            Aanmaken
                        </button>
                    </td>
                </tr>
                <?php
                $friendlyurls = $connectie->prepare('SELECT naam,doel FROM friendlyurls ORDER BY naam ASC;');
                $friendlyurls->execute();

                foreach ($friendlyurls as $friendlyurl)
                {
                    echo '<tr><td><div class="btn-group">';
                    echo new Knop('verwijderen', 'overzicht.php?type=friendlyurl&amp;actie=verwijderen&amp;naam=' . $friendlyurl['naam'], 'Verwijder deze friendly URL', null, 16);
                    echo new Knop('aanmenutoevoegen', 'overzicht.php?type=friendlyurl&amp;actie=aanmenutoevoegen&amp;naam=' . $friendlyurl['naam'], 'Voeg deze friendly url toe aan het menu', null, 16);
                    echo '</div></td><td><strong>' . $friendlyurl['naam'] . '</strong></td><td>' . $friendlyurl['doel'] . '</td></tr>';
                }
                ?>
            </table>
        </form>
        <?php
        $this->toonPostPagina();
    }
}