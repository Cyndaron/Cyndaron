<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed;

use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;

class Module implements Datatypes
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
}