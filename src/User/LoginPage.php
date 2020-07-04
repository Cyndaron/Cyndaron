<?php
namespace Cyndaron\User;

use Cyndaron\Page;

final class LoginPage extends Page
{
    public function __construct()
    {
        $csrfToken = User::getCSRFToken('user', 'login');
        parent::__construct('Inloggen');
        $this->addTemplateVars(['csrfToken' => $csrfToken]);
    }
}
