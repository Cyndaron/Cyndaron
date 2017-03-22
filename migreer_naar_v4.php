<?php
require('check.php');
require_once('functies.pagina.php');
require_once('functies.db.php');

#migreer hoofdstukken naar subs
$connectie = newPDO();
$hoofdstukken = $connectie->prepare('SELECT * FROM hoofdstukken ORDER BY id ASC;');
$hoofdstukken->execute();

foreach ($hoofdstukken as $hoofdstuk)
{
    $inhoud = "";
    $artikelen = $connectie->prepare('SELECT tekst FROM artikelen WHERE hid=? ORDER BY id DESC;');
    $artikelen->execute([$hoofdstuk['id']]);
    foreach ($artikelen as $artikel)
    {
        if ($inhoud != "")
        {
            $inhoud .= '<br /><br />';
        }
        $inhoud .= $artikel['tekst'];
    }

    $id = nieuweSub($hoofdstuk['naam'], $inhoud, 0, 0);
    voegToeAanMenu('toonsub.php?id=' . $id, "");

    $vorigeinhoud = "";
    $vorigeartikelen = $connectie->prepare('SELECT tekst FROM vorigeartikelen WHERE hid=? ORDER BY id ASC;');
    $vorigeartikelen->execute([$hoofdstuk['id']]);
    foreach ($vorigeartikelen as $vorigartikel)
    {
        if ($vorigeinhoud != "")
        {
            $vorigeinhoud .= '<br /><br />';
        }
        $vorigeinhoud .= $vorigartikel['tekst'];
    }

    geefEen('INSERT INTO vorigesubs(id,naam,tekst) VALUES (?,?,?);', [$id, $hoofdstuk['naam'], $vorigeinhoud]);
}

$categorieen = $connectie->prepare('SELECT id FROM categorieen ORDER BY id ASC;');
$categorieen->execute();

foreach ($categorieen as $categorie)
{
    voegToeAanMenu('tooncategorie.php?id=' . $categorie['id'], "");
}

if ($connectie->query('SELECT * FROM fotoboeken')->fetchColumn())
{
    voegToeAanMenu('tooncategorie.php?id=fotoboeken', "");
}

$extramenuitems = $connectie->prepare('SELECT naam,link FROM vastemenuitems ORDER BY id ASC;');
$extramenuitems->execute();
foreach ($extramenuitems as $extramenuitem)
{
    voegToeAanMenu($extramenuitem['link'], $extramenuitem['naam']);
}

geefEen('DROP TABLE vorigeartikelen', []);
geefEen('DROP TABLE artikelen', []);
geefEen('DROP TABLE hoofdstukken', []);
geefEen('DROP TABLE vastemenuitems', []);

echo 'Script voltooid';