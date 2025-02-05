<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop\Page;

use Cyndaron\Page\Page;

final class CreateAccountPage extends Page
{
    public function __construct(bool $skipTicketCheck)
    {
        $this->title = 'Account voor webwinkel aanmaken';
        $this->addTemplateVar('skipTicketCheck', $skipTicketCheck);
    }
}
