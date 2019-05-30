<?php
namespace Cyndaron\User;

use Cyndaron\Page;

class LoginPage extends Page
{
    public function __construct()
    {
        $csrfToken = User::getCSRFToken('user', 'login');
        parent::__construct('Inloggen');
        $this->showPrePage();
        echo '
<form class="form-horizontal" method="post" action="#">
<p>Als u inloggegevens hebt gekregen voor deze website, dan kunt u hieronder inloggen.</p>
<div class="form-group">
    <label for="login_user" class="control-label col-sm-2">Gebruikersnaam of e-mailadres</b>:</label>
    <div class="col-sm-3">
        <input type="text" class="form-control" id="login_user" name="login_user" required/>
    </div>
</div>
<div class="form-group">
    <label for="login_pass" class="control-label col-sm-2">Wachtwoord:</label>
    <div class="col-sm-3">
        <input type="password" class="form-control" id="login_pass" name="login_pass" required/>
    </div>
</div>
<input type="hidden" name="csrfToken" value="' . $csrfToken . '"/>
<input type="submit" class="btn btn-primary" name="submit" value="Inloggen" />
</form>
';
        $this->showPostPage();
    }
}