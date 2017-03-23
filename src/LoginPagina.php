<?php
namespace Cyndaron;


class LoginPagina extends Pagina
{
    public function __construct()
    {
        if (!Request::postIsLeeg())
        {
            if (Request::geefPostVeilig('login_naam') && Request::geefPostVeilig('login_wach'))
            {
                $login['naam'] = Request::geefPostVeilig('login_naam');
                $login['wach'] = hash('sha512', Request::geefPostVeilig('login_wach'));

                $this->connectie = DBConnection::getPDO();

                $prep = $this->connectie->prepare('SELECT * FROM gebruikers WHERE gebruikersnaam=?');
                $prep->execute(array($login['naam']));
                $userdata = $prep->fetch();

                if (!$userdata)
                {
                    parent::__construct('Fout');
                    $this->maakNietDelen(true);
                    $this->toonPrePagina();
                    echo 'Verkeerde gebruikersnaam.';
                    $this->toonPostPagina();
                }
                elseif ($userdata['wachtwoord'] !== $login['wach'])
                {
                    parent::__construct('Fout');
                    $this->maakNietDelen(true);
                    $this->toonPrePagina();
                    echo 'Verkeerd wachtwoord.';
                    $this->toonPostPagina();
                }
                elseif ($userdata['wachtwoord'] == $login['wach'] && $userdata['gebruikersnaam'] == $login['naam'])
                {
                    $_SESSION['naam'] = $login['naam'];
                    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
                    $_SESSION['niveau'] = $userdata['niveau'];
                    Gebruiker::nieuweMelding('U bent ingelogd.');
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
                    parent::__construct('Fout');
                    $this->maakNietDelen(true);
                    $this->toonPrePagina();
                    echo 'Er is een fout opgetreden.';
                    $this->toonPostPagina();
                }
            }
            else
            {
                parent::__construct('Fout');
                $this->maakNietDelen(true);
                $this->toonPrePagina();
                echo 'Verkeerde gebruikersnaam.';
                $this->toonPostPagina();
            }
        }
        else
        {
            if (empty($_SESSION['redirect']))
                $_SESSION['redirect'] = Request::geefReferrerVeilig();
            parent::__construct('Inloggen');
            $this->maakNietDelen(true);
            $this->toonPrePagina();
            echo '
<form class="form-horizontal" method="post" action="#">
<p>Dit is bedoeld voor beheerders om wijzigingen aan de pagina aan te brengen. Als u hier toevallig terecht bent gekomen kunt u hier niets doen. U kunt dan klikken op &eacute;&eacute;n van de onderdelen in het menu.</p>
<div class="form-group">
    <label for="login_naam" class="control-label col-sm-2">Gebruikersnaam:</label>
    <div class="col-sm-3">
        <input type="text" class="form-control" id="login_naam" name="login_naam" maxlength="20" />
    </div>
</div>
<div class="form-group">
    <label for="login_wach" class="control-label col-sm-2">Wachtwoord:</label>
    <div class="col-sm-3">
        <input type="password" class="form-control" id="login_wach" name="login_wach" maxlength="20" />
    </div>
</div>
<input type="submit" class="btn btn-primary" name="submit" value="Inloggen" />
</form>
';
            $this->toonPostPagina();
        }
    }
}