<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout;

use Cyndaron\User\User;
use Cyndaron\View\Page;

class UpdateFormPage extends Page
{
    public function __construct()
    {
        parent::__construct('Punten updaten');
        $this->addTemplateVar('csrfToken', User::getCSRFToken('tryout', 'update'));
    }
}
