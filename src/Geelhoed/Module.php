<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed;

use Cyndaron\Geelhoed\Hour\HourController;
use Cyndaron\Geelhoed\Location\Location;
use Cyndaron\Geelhoed\Location\LocationController;
use Cyndaron\Geelhoed\Member\MemberController;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;
use Cyndaron\Module\UrlProvider;

class Module implements Datatypes, Routes, UrlProvider
{
    /**
     * @return Datatype[]
     */
    public function dataTypes(): array
    {
        return [
            'location' => Datatype::fromArray([
                'singular' => 'Locatie',
                'plural' => 'Locaties',
                'pageManagerTab' => PageManagerTabs::class . '::locationsTab',
            ]),
            'member' => Datatype::fromArray([
                'singular' => 'Lid',
                'plural' => 'Leden',
                'pageManagerTab' => PageManagerTabs::class . '::membersTab',
            ]),
        ];
    }

    public function routes(): array
    {
        return [
            'hour' => HourController::class,
            'location' =>  LocationController::class,
            'member' => MemberController::class,
        ];
    }

    public function url(array $linkParts): ?string
    {
        switch ($linkParts[0])
        {
            case 'location':
                switch ($linkParts[1])
                {
                    case 'view':
                        $location = Location::loadFromDatabase((int)$linkParts[2]);
                        return $location->getName();
                }
        }

        return null;
    }
}