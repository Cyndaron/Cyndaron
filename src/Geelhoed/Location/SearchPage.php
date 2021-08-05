<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Location;

use Cyndaron\View\Page;

class SearchPage extends Page
{
    public function __construct()
    {
        parent::__construct('Lessen zoeken');

        $this->addScript('/src/Geelhoed/Location/js/SearchPage.js');

        $this->addTemplateVars([
            'cities' => Location::getCities(),
        ]);
    }
}
