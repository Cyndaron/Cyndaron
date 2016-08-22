<?php
function newPDO()
{
    $dbmethode = 'mysql';
    $dbuser = 'root';
    $dbpass = '';
    $dbplek = 'localhost';
    $dbnaam = 'cyndaron';
    require('instellingen.php');
    return new PDO($dbmethode . ':host=' . $dbplek . ';dbname=' . $dbnaam . ';charset=utf8', $dbuser, $dbpass);
}

function geefEen($query, $vars)
{
    $connectie = newPDO();
    $resultaat = $connectie->prepare($query);
    $resultaat->execute($vars);
    return $resultaat->fetchColumn();
}

function maakEen($query, $vars)
{
    $connectie = newPDO();
    $resultaat = $connectie->prepare($query);
    $resultaat->execute($vars);
    return $connectie->lastInsertId();
}

function geefInstelling($naam)
{
    $connectie = newPDO();
    $setting = $connectie->prepare('SELECT waarde FROM instellingen WHERE naam= ?');
    $setting->execute(array($naam));
    return htmlentities($setting->fetchColumn(), ENT_COMPAT | ENT_HTML5, 'UTF-8');
}

function maakInstelling($naam, $waarde)
{
    $connectie = newPDO();
    $setting = $connectie->prepare('DELETE FROM instellingen WHERE naam= ?');
    $setting->execute(array($naam));
    $setting = $connectie->prepare('INSERT INTO instellingen(`waarde`,`naam`) VALUES (?, ?)');
    $setting->execute(array($waarde, $naam));
}
