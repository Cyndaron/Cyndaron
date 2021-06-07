<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Member;

use Cyndaron\View\Page;

class DirectDebitListPage extends Page
{
    public function __construct(array $directDebits)
    {
        parent::__construct('Incassolijst');
        $this->addTemplateVars([
            'directDebits' => $directDebits,
        ]);
    }
}
