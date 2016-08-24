<?php
require_once('pagina.php');
require_once('functies.db.php');

if (!$_SESSION)
    session_start();

if (!empty($_POST))
{
    if (!empty($_POST['login_naam']) && !empty($_POST['login_wach']))
    {
        $login['naam'] = htmlentities($_POST['login_naam'], ENT_QUOTES, 'UTF-8');
        $login['wach'] = hash('sha512', $_POST['login_wach']);

        $connectie = newPDO();

        $prep = $connectie->prepare('SELECT * FROM gebruikers WHERE gebruikersnaam=?');
        $prep->execute(array($login['naam']));
        $userdata = $prep->fetch();

        if (!$userdata)
        {
            $pagina = new Pagina('Fout');
            $pagina->maakNietDelen(true);
            $pagina->toonPrePagina();
            echo 'Verkeerde gebruikersnaam.';
            $pagina->toonPostPagina();
        }
        elseif ($userdata['wachtwoord'] !== $login['wach'])
        {
            $pagina = new Pagina('Fout');
            $pagina->maakNietDelen(true);
            $pagina->toonPrePagina();
            echo 'Verkeerd wachtwoord.';
            $pagina->toonPostPagina();
        }
        elseif ($userdata['wachtwoord'] == $login['wach'] && $userdata['gebruikersnaam'] == $login['naam'])
        {
            $_SESSION['naam'] = $login['naam'];
            $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['niveau'] = $userdata['niveau'];
            nieuweMelding('U bent ingelogd.');
            if ($_SESSION['redirect'])
            {
                $_SESSION['request'] = $_SESSION['redirect'];
                $_SESSION['redirect'] = null;
            }
            else
                $_SESSION['request'] = '/';
            header('Location: ' . $_SESSION['request']);

        }
        else
        {
            $pagina = new Pagina('Fout');
            $pagina->maakNietDelen(true);
            $pagina->toonPrePagina();
            echo 'Er is een fout opgetreden.';
            $pagina->toonPostPagina();
        }
    }
    else
    {
        $pagina = new Pagina('Fout');
        $pagina->maakNietDelen(true);
        $pagina->toonPrePagina();
        echo 'Verkeerde gebruikersnaam.';
        $pagina->toonPostPagina();
    }
}
else
{
    if (empty($_SESSION['redirect']))
        $_SESSION['redirect'] = geefReferrerVeilig();
    $pagina = new Pagina('Inloggen');
    $pagina->maakNietDelen(true);
    $pagina->toonPrePagina();
    echo '
<form class="form" method="post" action="#">
<p>Dit is bedoeld voor beheerders om wijzigingen aan de pagina aan te brengen. Als u hier toevallig terecht bent gekomen kunt u hier niets doen. U kunt dan klikken op &eacute;&eacute;n van de onderdelen in het menu.</p>
<div class="form-group">
    <label for="login_naam">Gebruikersnaam:</label>
    <input type="text" class="form-control form-control-inline" id="login_naam" name="login_naam" maxlength="20" />
</div>
<div class="form-group">
    <label for="login_wach">Wachtwoord:</label>
    <input type="password" class="form-control form-control-inline" id="login_wach" name="login_wach" maxlength="20" />
</div>
<input type="submit" class="btn btn-primary" name="submit" value="Inloggen" />
</form>
';
    $pagina->toonPostPagina();
}
?>
