<?php
namespace Cyndaron;

use Cyndaron\User\User;

class LoginPagina extends Pagina
{
    public function __construct()
    {
        if (!Request::postIsLeeg())
        {
            if (Request::geefPostVeilig('login_user') && Request::geefPostVeilig('login_pass'))
            {
                $identification = Request::geefPostVeilig('login_user');
                $password = Request::geefPostVeilig('login_pass');
                $password512 = hash('sha512', $password);

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

                $userdata = DBConnection::doQueryAndFetchFirstRow($query, [$identification]);

                if (!$userdata)
                {
                    parent::__construct('Fout');
                    $this->showPrePage();
                    echo 'Onbekende gebruikersnaam of e-mailadres.';
                    $this->showPostPage();
                }
                else
                {
                    $loginSucceeded = false;

                    if (password_verify($password, $userdata['wachtwoord']))
                    {
                        $loginSucceeded = true;

                        if (password_needs_rehash($userdata['wachtwoord'], PASSWORD_DEFAULT))
                        {
                            $password = password_hash($password, PASSWORD_DEFAULT);
                            DBConnection::doQuery($updateQuery, [$password, $identification]);
                        }
                    }
                    elseif ($userdata['wachtwoord'] == $password512)
                    {
                        $loginSucceeded = true;

                        $password = password_hash($password, PASSWORD_DEFAULT);
                        DBConnection::doQuery($updateQuery, [$password, $identification]);
                    }

                    if ($loginSucceeded)
                    {
                        $_SESSION['naam'] = $userdata['gebruikersnaam'];
                        $_SESSION['email'] = $userdata['email'];
                        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
                        $_SESSION['niveau'] = $userdata['niveau'];
                        User::addNotification('U bent ingelogd.');
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
                        $this->showPrePage();
                        echo 'Verkeerd wachtwoord.';
                        $this->showPostPage();
                    }
                }
            }
            else
            {
                parent::__construct('Fout');
                $this->showPrePage();
                echo 'Verkeerde gebruikersnaam of e-mailadres.';
                $this->showPostPage();
            }
        }
        else
        {
            if (empty($_SESSION['redirect']))
            {
                $_SESSION['redirect'] = Request::geefReferrerVeilig();
            }
            parent::__construct('Inloggen');
            $this->showPrePage();
            echo '
<form class="form-horizontal" method="post" action="#">
<p>Als u inloggegevens hebt gekregen voor deze website, dan kunt u hieronder inloggen.</p>
<div class="form-group">
    <label for="login_user" class="control-label col-sm-2">Gebruikersnaam of e-mailadres</b>:</label>
    <div class="col-sm-3">
        <input type="text" class="form-control" id="login_user" name="login_user"/>
    </div>
</div>
<div class="form-group">
    <label for="login_pass" class="control-label col-sm-2">Wachtwoord:</label>
    <div class="col-sm-3">
        <input type="password" class="form-control" id="login_pass" name="login_pass"/>
    </div>
</div>
<input type="submit" class="btn btn-primary" name="submit" value="Inloggen" />
</form>
';
            $this->showPostPage();
        }
    }
}