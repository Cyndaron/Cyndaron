<?php
namespace Cyndaron\User;

use Cyndaron\Page\Page;
use Cyndaron\Translation\Translator;

final class LoginPage extends Page
{
    public function __construct(CSRFTokenHandler $tokenHandler, Translator $t)
    {
        $csrfToken = $tokenHandler->get('user', 'login');
        parent::__construct($t->get('Inloggen'));
        $this->addTemplateVars(['csrfToken' => $csrfToken]);
    }
}
