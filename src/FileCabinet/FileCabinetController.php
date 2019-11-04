<?php
declare (strict_types = 1);

namespace Cyndaron\FileCabinet;

use Cyndaron\FileCabinet\OverviewPage;
use Cyndaron\Controller;

class FileCabinetController extends Controller
{
    protected function routeGet()
    {
        new OverviewPage();
    }
}