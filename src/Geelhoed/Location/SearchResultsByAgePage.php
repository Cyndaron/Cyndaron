<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Location;

use Cyndaron\Geelhoed\Hour\Hour;
use Cyndaron\View\Page;

class SearchResultsByAgePage extends Page
{
    public function __construct(int $age)
    {
        parent::__construct('Lessen zoeken');

        $this->addTemplateVars([
            'age' => $age,
            'hours' => Hour::fetchByAge($age),
        ]);
    }
}
