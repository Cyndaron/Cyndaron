<?php

$boekid = htmlentities($_GET['boekid'], null, 'UTF-8');
if (!is_numeric($boekid) || $boekid < 1)
{
    header("Location: 404.php");
    die('Incorrecte parameter ontvangen.');
}
else
{
    header('Location: toonfotoboek.php?id=' . $boekid);
    die('Dit is een oude link naar een foto, die niet meer werkt.');
}