<?php
namespace Cyndaron\User;

use Cyndaron\Page\Page;

final class ChangePasswordPage extends Page
{
    public function __construct(CSRFTokenHandler $tokenHandler)
    {
        parent::__construct('Wachtwoord wijzigen');
        $this->addTemplateVar('csrfToken', $tokenHandler->get('user', 'changePassword'));
    }
}
