<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Volunteer;

enum TryoutHelpType : string
{
    case TAFELMEDEWERKER = 'tafelmedewerker';
    case SCHEIDSRECHTER = 'scheidsrechter';
    case GROEPJESBEGELEIDER = 'groepjesbegeleider';

    public static function getFriendlyNames(): array
    {
        return [
            self::TAFELMEDEWERKER->value => 'Tafelmedewerker',
            self::SCHEIDSRECHTER->value => 'Scheidsrechter',
            self::GROEPJESBEGELEIDER->value => 'Groepsjesbegeleider',
        ];
    }
}
