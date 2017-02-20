<?php
/**
 * Functies voor het beheren van pagina's en elementen erop.
 */
function nieuweCategorie($naam, $alleentitel = false, $beschrijving = '')
{
    return maakEen('INSERT INTO categorieen(`naam`,`alleentitel`, `beschrijving`) VALUES (?,?,?);', array($naam, (int)$alleentitel, $beschrijving));
}

function nieuwFotoalbum($naam, $notities = "")
{
    return maakEen('INSERT INTO fotoboeken(`naam`,`notities`) VALUES (?,?);', array($naam, $notities));
}

function wijzigCategorie($id, $naam = null, $alleentitel = null, $beschrijving = null)
{
    if ($naam !== null)
        geefEen('UPDATE categorieen SET `naam`=? WHERE id=?', array($naam, $id));
    if ($alleentitel !== null)
        geefEen('UPDATE categorieen SET `alleentitel`=? WHERE id=?', array(parseCheckboxAlsInt($alleentitel), $id));
    if ($beschrijving !== null)
        geefEen('UPDATE categorieen SET `beschrijving`=? WHERE id=?', array($beschrijving, $id));
}

function wijzigFotoalbum($id, $naam = null, $notities = null)
{
    if ($naam !== null)
        geefEen('UPDATE fotoboeken SET `naam`=? WHERE id=?', array($naam, $id));
    if ($notities !== null)
        geefEen('UPDATE fotoboeken SET `notities`=? WHERE id=?', array($notities, $id));
}

function verwijderCategorie($id)
{
    geefEen('DELETE FROM categorieen WHERE id=?;', array($id));
}

function verwijderFotoalbum($id)
{
    geefEen('DELETE FROM fotoboeken WHERE id=?;', array($id));
}

function nieuweSub($titel, $tekst, $reacties_aan, $categorieid)
{
    if (!$reacties_aan)
        $reacties_aan = '0';
    else
        $reacties_aan = '1';

    $connectie = newPDO();
    $prep = $connectie->prepare('INSERT INTO subs(naam, tekst, reacties_aan, categorieid) VALUES ( ?, ?, ?, ?)');
    $prep->execute(array($titel, $tekst, $reacties_aan, $categorieid));
    return $connectie->lastInsertId();
}

function wijzigSub($id, $titel, $tekst, $reacties_aan, $categorieid)
{
    $reacties_aan = parseCheckboxAlsInt($reacties_aan);
    $connectie = newPDO();
    if (!geefEen('SELECT * FROM vorigesubs WHERE id=?', array($id)))
    {
        $prep = $connectie->prepare('INSERT INTO vorigesubs VALUES (?, \'\', \'\')');
        $prep->execute(array($id));
    }
    $prep = $connectie->prepare('UPDATE vorigesubs SET tekst=( SELECT tekst FROM subs WHERE id=? ) WHERE id=?');
    $prep->execute(array($id, $id));
    $prep = $connectie->prepare('UPDATE vorigesubs SET naam=( SELECT naam FROM subs WHERE id=? ) WHERE id=?');
    $prep->execute(array($id, $id));

    $prep = $connectie->prepare('UPDATE subs SET tekst= ?, naam= ?, reacties_aan=?, categorieid= ? WHERE id= ?');
    $prep->execute(array($tekst, $titel, $reacties_aan, $categorieid, $id));
}

function verwijderSub($id)
{
    geefEen('DELETE FROM subs WHERE id=?;', array($id));
    geefEen('DELETE FROM vorigesubs WHERE id=?;', array($id));
}


function maakBijschrift($hash, $bijschrift)
{
    geefEen('DELETE FROM bijschriften WHERE hash = ?', array($hash));
    geefEen('INSERT INTO bijschriften(hash,bijschrift) VALUES (?,?)', array($hash, $bijschrift));
}

function parseCheckboxAlsInt($waarde)
{
    if (!$waarde)
        return 0;
    else
        return 1;
}

function parseCheckBoxAlsBool($waarde)
{
    if (!$waarde)
        return false;
    else
        return true;
}

function geefPaginanaam($link)
{
    $link = geefUnfriendlyUrl($link);
    $pos = strrpos($link, '/', -1);
    $laatstedeel = substr($link, $pos);
    $split = explode('?', $laatstedeel);
    $vars = explode('&', $split[1]);
    $values = null;
    foreach ($vars as $var)
    {
        $temp = explode('=', $var);
        $values[$temp[0]] = $temp[1];
    }
    switch ($split[0])
    {
        case 'toonhoofdstuk.php':
            $sql = 'SELECT naam FROM hoofdstukken WHERE id=?';
            break;
        case 'toonsub.php':
            $sql = 'SELECT naam FROM subs WHERE id=?';
            break;
        case 'tooncategorie.php':
            if ($values['id'] == 'fotoboeken')
                return 'Fotoboeken';
            else
                $sql = 'SELECT naam FROM categorieen WHERE id=?';
            break;
        case 'toonfotoboek.php':
            $sql = 'SELECT naam FROM fotoboeken WHERE id=?';
            break;
    }
    if ($naam = geefEen($sql, array($values['id'])))
        return $naam;
    elseif ($naam = geefEen('SELECT naam FROM friendlyurls WHERE link=?', array($link)))
        return $naam;
    else
        return $link;
}

function knopcode($soort, $link, $beschrijving = null, $tekst = null, $formaat = 20)
{
    switch ($soort)
    {
        case 'nieuw':
            $pictogram = 'plus';
            break;
        case 'bewerken':
            $pictogram = 'pencil';
            break;
        case 'verwijderen':
            $pictogram = 'trash';
            break;
        case 'vorigeversie':
            $pictogram = 'vorige-versie';
            break;
        case 'aanmenutoevoegen':
            $pictogram = 'bookmark';
            break;
        default:
            $pictogram = $soort;
    }

    switch ($formaat)
    {
        case 16:
            $btnClass = 'btn-sm';
            break;
        default:
            $btnClass = '';
    }

    $title = $beschrijving ? 'title="' . $beschrijving . '"' : '';
    $tekstNaPictogram = $tekst ? ' ' . $tekst : '';
    return sprintf('<a class="btn btn-default %s" href="%s" %s><span class="glyphicon glyphicon-%s"></span>%s</a>', $btnClass, $link, $title, $pictogram, $tekstNaPictogram);
}

function knop($soort, $link, $beschrijving = null, $tekst = null, $formaat = 20)
{
    echo knopcode($soort, $link, $beschrijving, $tekst, $formaat);
}

function woordlimiet($string, $lengte = 50, $ellips = "...")
{
    $string = strip_tags($string);
    $words = explode(' ', $string);
    if (count($words) > $lengte)
        return implode(' ', array_slice($words, 0, $lengte)) . $ellips;
    else
        return $string;
}

function toonIndienAanwezig($string, $voor = null, $na = null)
{
    if ($string)
    {
        echo $voor;
        echo $string;
        echo $na;
    }
}

function vervangMenu($nieuwmenu)
{
    geefEen('DELETE FROM menu;', array());

    if (count($nieuwmenu) > 0)
    {
        $teller = 1;
        foreach ($nieuwmenu as $menuitem)
        {
            geefEen('INSERT INTO menu(volgorde,link,alias) VALUES(?,?,?);', array($teller, $menuitem['link'], $menuitem['alias']));
            $teller++;
        }
    }
}

function voegToeAanMenu($link, $alias = "")
{
    $teller = geefEen('SELECT MAX(volgorde) FROM menu;', array()) + 1;
    geefEen('INSERT INTO menu(volgorde,link,alias) VALUES(?,?,?);', array($teller, $link, $alias));
}

function parseTextForInlineImages($text)
{
    return preg_replace_callback('/src="(data\:)(.*)"/', 'extractImages', $text);
}

function extractImages($matches)
{
    list($type, $image) = explode(';', $matches[2]);

    switch($type)
    {
        case 'image/gif':
            $extensie = 'gif';
            break;
        case 'image/jpeg':
            $extensie = 'jpg';
            break;
        case 'image/png':
            $extensie = 'png';
            break;
        case 'image/bmp':
            $extensie = 'bmp';
            break;
        default:
            return 'src="' . $matches[0] . '"';
    }

    $image = str_replace('base64', '', $image);
    $image = base64_decode(str_replace(' ', '+', $image));
    $uploadDir = './afb/via-editor/';
    $destinationFilename = $uploadDir . date('c') . '-' . md5($image) . '.' . $extensie;
    @mkdir($uploadDir, 0777, TRUE);
    file_put_contents($destinationFilename, $image);

    return 'src="' . $destinationFilename . '"';
}
