<?php
namespace Cyndaron\User;

use Cyndaron\Page;

class LoginPage extends Page
{
    public function __construct()
    {
        $csrfToken = User::getCSRFToken('user', 'login');
        parent::__construct('Inloggen');
        $this->addTemplateVars(compact('csrfToken'));
    }
}
