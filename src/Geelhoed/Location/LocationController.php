<?php
namespace Cyndaron\Geelhoed\Location;

use Cyndaron\Controller;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;

class LocationController extends Controller
{
    protected array $getRoutes = [
        'view' => ['level' => UserLevel::ANONYMOUS, 'function' => 'view'],
        'overview' => ['level' => UserLevel::ANONYMOUS, 'function' => 'overview']
    ];

    public function view()
    {
        $id = (int)Request::getVar(2);
        $location = Location::loadFromDatabase($id);

        if ($location)
        {
            new LocationPage($location);
        }
    }

    public function overview()
    {
        new LocationOverview();
    }
}