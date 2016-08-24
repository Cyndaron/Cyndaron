<?php
require('check.php');
require_once('functies.pagina.php');
require_once('functies.db.php');
require_once('pagina.php');

$type = geefGetVeilig('type');
$actie = geefGetVeilig('actie');
$id = geefGetVeilig('id');
$zeker = geefGetVeilig('zeker');
$naam = geefPostVeilig('naam');

if ($type == 'categorie')
{
    if ($actie == 'nieuw')
    {
        nieuweCategorie($naam);
    }
    elseif ($actie == 'bewerken')
    {
        wijzigCategorie($id, $naam);
    }
    elseif ($actie == 'verwijderen' && $zeker == 1)
    {
        verwijderCategorie($id);
    }
    elseif ($actie == 'aanmenutoevoegen')
    {
        voegToeAanMenu('tooncategorie.php?id=' . $id);
    }
}
elseif ($type == 'fotoboek')
{
    if ($actie == 'nieuw')
    {
        nieuwFotoalbum($naam);
    }
    elseif ($actie == 'bewerken')
    {
        wijzigFotoalbum($id, $naam);
    }
    elseif ($actie == 'verwijderen' && $zeker == 1)
    {
        verwijderFotoalbum($id);
    }
    elseif ($actie == 'aanmenutoevoegen')
    {
        voegToeAanMenu('toonfotoboek.php?id=' . $id);
    }
}
elseif ($type == 'sub')
{
    if ($actie == 'verwijderen' && $zeker == 1)
    {
        verwijderSub($id);
    }
    elseif ($actie == 'aanmenutoevoegen')
    {
        voegToeAanMenu('toonsub.php?id=' . $id);
    }

}
elseif ($type == 'friendlyurl')
{
    if ($actie == 'nieuw')
    {
        $doel = geefPostVeilig('doel');
        maakFriendlyUrl($naam, $doel);
    }
    elseif ($actie == 'verwijderen' && $zeker == 1)
    {
        $naam = geefGetVeilig('naam');
        verwijderFriendlyUrl($naam);
    }
    elseif ($actie == 'aanmenutoevoegen')
    {
        $naam = geefGetVeilig('naam');
        voegToeAanMenu($naam);
    }
}

$pagina = new Pagina('Paginaoverzicht');
$pagina->maakNietDelen(true);
$pagina->toonPrepagina();
$connectie = newPDO();

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
echo '<h2>Statische pagina\'s (subs)</h2>';

knop('nieuw', 'editor.php?type=sub', 'Nieuwe sub', 'Nieuwe sub');

$subs = $connectie->prepare('SELECT id, naam, "Zonder categorie" AS categorie FROM subs WHERE categorieid NOT IN (SELECT id FROM categorieen) UNION (SELECT s.id AS id, s.naam AS naam, c.naam AS categorie FROM subs AS s,categorieen AS c WHERE s.categorieid=c.id ORDER BY categorie, naam, id ASC);');
$subs->execute();
$laatstecategorie = "";
$subsPerCategorie = array();

foreach ($subs->fetchAll() as $sub)
{
    if (empty($subsPerCategorie[$sub['categorie']]))
        $subsPerCategorie[$sub['categorie']] = array();

    $subsPerCategorie[$sub['categorie']][$sub['id']] = $sub['naam'];
}

foreach ($subsPerCategorie as $categorie => $subs)
{
    echo '<h3 class="text-italic">' . $categorie . '</h3>';
    echo '<table class="table table-striped table-bordered table-overzicht">';

    foreach ($subs as $subId => $subNaam)
    {
        echo '<tr><td><div class="btn-group">';
        knop('bewerken', 'editor.php?type=sub&amp;id=' . $subId, 'Bewerk deze sub', null, 16);
        knop('verwijderen', 'overzicht.php?type=sub&amp;actie=verwijderen&amp;id=' . $subId, 'Verwijder deze sub', null, 16);
        knop('aanmenutoevoegen', 'overzicht.php?type=sub&amp;actie=aanmenutoevoegen&amp;id=' . $subId, 'Voeg deze sub toe aan het menu', null, 16);
        $vvsub = $connectie->prepare('SELECT * FROM vorigesubs WHERE id= ?');
        $vvsub->execute(array($subId));

        if ($vvsub->fetchColumn())
        {
            knop('vorigeversie', 'editor.php?type=sub&amp;vorigeversie=1&amp;id=' . $subId, 'Vorige versie terugzetten', null, 16);
        }
        echo '</div></td><td>';
        $subNaam = strtr($subNaam, array(' ' => '&nbsp;'));
        echo '<span style="font-size: 15px;">';
        echo '<a href="toonsub.php?id=' . $subId . '"><b>' . $subNaam . '</b></a>';
        echo "</span></td></tr>\n";
    }

    echo  '</table>';
}
?>

    <!-- Categorieën -->
    <h2>Categorieën</h2>
    <form method="post" action="overzicht.php?type=categorie&amp;actie=nieuw" class="form-inline">
        <div class="form-group">
            <label for="naam">Nieuwe categorie:</label> <input class="form-control" id="naam" name="naam" type="text"/>
        </div>
        <input class="btn btn-default" type="submit" value="Aanmaken"/>
    </form>
    <br />
    <table class="table table-striped table-bordered table-overzicht"><?php
        $categorieen = $connectie->prepare('SELECT id,naam FROM categorieen ORDER BY id ASC;');
        $categorieen->execute();
        foreach ($categorieen as $categorie)
        {
            ?>
            <tr>
                <td><div class="btn-group"><?php
                    knop('bewerken', 'editor.php?type=categorie&amp;id=' . $categorie['id'], 'Deze categorie bewerken', null, 16);
                    knop('verwijderen', 'overzicht.php?type=categorie&amp;actie=verwijderen&amp;id=' . $categorie['id'], 'Verwijder deze categorie', null, 16);
                    knop('aanmenutoevoegen', 'overzicht.php?type=categorie&amp;actie=aanmenutoevoegen&amp;id=' . $categorie['id'], 'Voeg deze categorie toe aan het menu', null, 16); ?>
                </div></td>
                <td>
                    <a href="tooncategorie.php?id=<?php echo $categorie['id']; ?>"><b><?php echo $categorie['naam']; ?></b></a>
                </td>
            </tr>
        <?php } ?>
    </table>

    <!-- Fotoboeken -->
    <h2>Fotoboeken</h2>
    <form method="post" action="overzicht.php?type=fotoboek&amp;actie=nieuw">
        Nieuw fotoboek: <input name="naam" type="text"/><input type="submit" value="Aanmaken"/>
    </form>
    <table><?php
        $fotoboeken = $connectie->prepare('SELECT id,naam FROM fotoboeken ORDER BY id ASC;');
        $fotoboeken->execute();
        foreach ($fotoboeken as $fotoboek)
        {
            ?>
            <tr>
                <td style="vertical-align: bottom;"><?php
                    knop('bewerken', 'editor.php?type=fotoboek&amp;id=' . $fotoboek['id'], 'Bewerk dit fotoboek', null, 16);
                    knop('verwijderen', 'overzicht.php?type=fotoboek&amp;actie=verwijderen&amp;id=' . $fotoboek['id'], 'Verwijder dit fotoboek', null, 16);
                    knop('aanmenutoevoegen', 'overzicht.php?type=fotoboek&amp;actie=aanmenutoevoegen&amp;id=' . $fotoboek['id'], 'Voeg dit fotoboek toe aan het menu', null, 16); ?>
                </td>
                <td style="vertical-align: middle; font-size: 15px;">
                    <form method="post"
                          action="overzicht.php?type=fotoboek&amp;actie=bewerken&amp;id=<?php echo $fotoboek['id']; ?>">
			<span style="text-align: middle;" id="fotoboek-<?php echo $fotoboek['id']; ?>-oud">
				<a href="toonfotoboek.php?id=<?php echo $fotoboek['id']; ?>"><b><?php echo $fotoboek['naam']; ?></b></a> (mapnummer <?php echo $fotoboek['id']; ?>
                )
			</span>
			<span id="fotoboek-<?php echo $fotoboek['id']; ?>-nieuw" style="display:none;">
				<input name="naam" value="<?php echo $fotoboek['naam']; ?>"/>
			</span>
			<span style="vertical-align:bottom;">
				<button id="fotoboek-<?php echo $fotoboek['id']; ?>-nieuw-opslaan" style="display:none;" class="sys"
                        type="submit">
                    <img alt="" class="sys" style="height:16px; width: 16px;"
                         src="sys/pictogrammen/mono/accepteren.png"/>
                </button><button id="fotoboek-<?php echo $fotoboek['id']; ?>-nieuw-annuleren" style="display:none;"
                                 class="sys" type="button"
                                 onclick="wissel(false,'fotoboek-<?php echo $fotoboek['id']; ?>');">
                    <img alt="" class="sys" style="height:16px; width: 16px;"
                         src="sys/pictogrammen/mono/annuleren.png"/>
                </button>
			</span>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table><br/>
    <h2>Friendly URL's</h2>
    <form method="post" action="overzicht.php?type=friendlyurl&amp;actie=nieuw">
        <table>
            <tr>
                <td colspan="100%">Nieuwe friendly URL:</td>
            </tr>
            <tr>
                <td style="vertical-align: bottom;">URL: <input name="naam" type="text"/></td>
                <td style="vertical-align: bottom;">Verwijzingsdoel: <input name="doel" type="text"/></td>
                <td>
                    <button class="sys" type="submit">
                        <img alt="" class="sys" style="height:16px; width: 16px;"
                             src="<?php echo geefPictogram('accepteren') ?>"/></button>
                </td>
            </tr>
        </table>
    </form><?php
$friendlyurls = $connectie->prepare('SELECT naam,doel FROM friendlyurls ORDER BY naam ASC;');
$friendlyurls->execute();
echo '<br /><table><tr><th></th><th>URL</th><th>Verwijzingsdoel</th></tr>';
foreach ($friendlyurls as $friendlyurl)
{
    echo '<tr><td>';
    knop('verwijderen', 'overzicht.php?type=friendlyurl&amp;actie=verwijderen&amp;naam=' . $friendlyurl['naam'], 'Verwijder deze friendly URL', null, 16);
    knop('aanmenutoevoegen', 'overzicht.php?type=friendlyurl&amp;actie=aanmenutoevoegen&amp;naam=' . $friendlyurl['naam'], 'Voeg deze friendly url toe aan het menu', null, 16);
    echo '</td><td style="vertical-align: middle;"><strong>' . $friendlyurl['naam'] . '</strong></td><td style="vertical-align: middle;">' . $friendlyurl['doel'] . '</td></tr>';
}
?>
    </table>
    <script type="text/javascript" src="/sys/js/pagina-overzicht.js"></script>
<?php
$pagina->toonPostPagina();