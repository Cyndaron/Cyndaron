<?php
namespace Cyndaron\User;

use Cyndaron\Page\Page;

final class LoginPage extends Page
{
    public function __construct()
    {
        $csrfToken = UserSession::getCSRFToken('user', 'login');
        parent::__construct('Inloggen');
        $this->addTemplateVars(['csrfToken' => $csrfToken]);
    }
}
