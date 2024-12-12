<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop;

use Cyndaron\Page\Page;

final class CreateAccountPage extends Page
{
    public function __construct(bool $skipTicketCheck)
    {
        parent::__construct('Account voor webwinkel aanmaken');
        $this->addTemplateVar('skipTicketCheck', $skipTicketCheck);
    }
}
