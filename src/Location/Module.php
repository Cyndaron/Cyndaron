<?php
declare(strict_types=1);

namespace Cyndaron\Location;

use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\UrlProvider;

final class Module implements Datatypes, UrlProvider
{
    public function dataTypes(): array
    {
        return [
            'location' => Datatype::fromArray([
                'singular' => 'Locatie',
                'plural' => 'Locaties',
                'pageManagerTab' => PageManagerTabs::class . '::locationsTab',
            ]),
        ];
    }

    public function url(array $linkParts): string|null
    {
        if ($linkParts[0] === 'location' && $linkParts[1] === 'view')
        {
            $location = Location::fetchById((int)$linkParts[2]);
            if ($location === null)
            {
                return null;
            }
            return $location->getName();
        }

        return null;
    }
}
