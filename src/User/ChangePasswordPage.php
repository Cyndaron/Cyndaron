<?php
namespace Cyndaron\User;

use Cyndaron\Page;

final class ChangePasswordPage extends Page
{
    public function __construct()
    {
        parent::__construct('Wachtwoord wijzigen');
        $this->addTemplateVar('csrfToken', User::getCSRFToken('user', 'changePassword'));
    }
}