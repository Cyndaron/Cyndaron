<?php
namespace Cyndaron\User;

use Cyndaron\Page\Page;

final class ChangePasswordPage extends Page
{
    public function __construct(CSRFTokenHandler $tokenHandler)
    {
        $this->title = 'Wachtwoord wijzigen';
        $this->addTemplateVar('csrfToken', $tokenHandler->get('user', 'changePassword'));
    }
}
