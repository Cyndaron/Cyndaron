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
                $identification = Request::geefPostVeilig('login_naam');
                $wachtwoord = Request::geefPostVeilig('login_wach');
                $wachtwoord512 = hash('sha512', $wachtwoord);

                $connectie = DBConnection::getPDO();
                $connObj = DBConnection::getInstance();

                if (strpos($identification, '@') !== false)
                {
                    $query = 'SELECT * FROM gebruikers WHERE email=?';
                    $updateQuery = 'UPDATE gebruikers SET wachtwoord=? WHERE email=?';
                }
                else
                {
                    $query = 'SELECT * FROM gebruikers WHERE gebruikersnaam=?';
                    $updateQuery = 'UPDATE gebruikers SET wachtwoord=? WHERE gebruikersnaam=?';
                }


                $prep = $connectie->prepare($query);
                $prep->execute([$identification]);
                $userdata = $prep->fetch();

                if (!$userdata)
                {
                    parent::__construct('Fout');
                    $this->maakNietDelen(true);
                    $this->toonPrePagina();
                    echo 'Onbekende gebruikersnaam of e-mailadres.';
                    $this->toonPostPagina();
                }
                else
                {
                    $loginGelukt = false;

                    if (password_verify($wachtwoord, $userdata['wachtwoord']))
                    {
                        $loginGelukt = true;

                        if (password_needs_rehash($userdata['wachtwoord'], PASSWORD_DEFAULT))
                        {
                            $wachtwoord = password_hash($wachtwoord, PASSWORD_DEFAULT);
                            $connObj->doQuery($updateQuery, [$wachtwoord, $identification]);
                        }
                    }
                    elseif ($userdata['wachtwoord'] == $wachtwoord512)
                    {
                        $loginGelukt = true;

                        $wachtwoord = password_hash($wachtwoord, PASSWORD_DEFAULT);
                        $connObj->doQuery($updateQuery, [$wachtwoord, $identification]);
                    }

                    if ($loginGelukt)
                    {
                        $_SESSION['naam'] = $userdata['gebruikersnaam'];
                        $_SESSION['email'] = $userdata['email'];
                        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
                        $_SESSION['niveau'] = $userdata['niveau'];
                        Gebruiker::nieuweMelding('U bent ingelogd.');
                        if ($_SESSION['redirect'])
                        {
                            $_SESSION['request'] = $_SESSION['redirect'];
                            $_SESSION['redirect'] = null;
                        }
                        else
                        {
                            $_SESSION['request'] = '/';
                        }
                        header('Location: ' . $_SESSION['request']);
                    }
                    else
                    {
                        parent::__construct('Fout');
                        $this->maakNietDelen(true);
                        $this->toonPrePagina();
                        echo 'Verkeerd wachtwoord.';
                        $this->toonPostPagina();
                    }
                }
            }
            else
            {
                parent::__construct('Fout');
                $this->maakNietDelen(true);
                $this->toonPrePagina();
                echo 'Verkeerde gebruikersnaam of e-mailadres.';
                $this->toonPostPagina();
            }
        }
        else
        {
            if (empty($_SESSION['redirect']))
            {
                $_SESSION['redirect'] = Request::geefReferrerVeilig();
            }
            parent::__construct('Inloggen');
            $this->maakNietDelen(true);
            $this->toonPrePagina();
            echo '
<form class="form-horizontal" method="post" action="#">
<p>Als u inloggegevens hebt gekregen voor deze website, dan kunt u hieronder inloggen.</p>
<div class="form-group">
    <label for="login_naam" class="control-label col-sm-2">Gebruikersnaam of e-mailadres</b>:</label>
    <div class="col-sm-3">
        <input type="text" class="form-control" id="login_naam" name="login_naam"/>
    </div>
</div>
<div class="form-group">
    <label for="login_wach" class="control-label col-sm-2">Wachtwoord:</label>
    <div class="col-sm-3">
        <input type="password" class="form-control" id="login_wach" name="login_wach"/>
    </div>
</div>
<input type="submit" class="btn btn-primary" name="submit" value="Inloggen" />
</form>
';
            $this->toonPostPagina();
        }
    }
}