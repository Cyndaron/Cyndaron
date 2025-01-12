<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout;

use Cyndaron\Page\Page;

class ScoresFormPage extends Page
{
    public function __construct()
    {
        $this->title = 'Punten opvragen';

        $this->addScript('/src/Geelhoed/Tryout/js/ScoresFormPage.js');
    }
}
