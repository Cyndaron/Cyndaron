<?php
declare (strict_types = 1);

namespace Cyndaron\FileCabinet;

use Cyndaron\Bestandenkast\OverviewPage;
use Cyndaron\Controller;

class FileCabinetController extends Controller
{
    protected function routeGet()
    {
        new OverviewPage();
    }
}