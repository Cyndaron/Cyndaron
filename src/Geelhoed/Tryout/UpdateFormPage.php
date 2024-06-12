<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout;

use Cyndaron\Page\Page;
use Cyndaron\User\CSRFTokenHandler;

class UpdateFormPage extends Page
{
    public function __construct(CSRFTokenHandler $tokenHandler)
    {
        parent::__construct('Punten updaten');
        $this->addTemplateVar('csrfToken', $tokenHandler->get('tryout', 'update'));
    }
}
