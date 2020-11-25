<?php
namespace Cyndaron\Geelhoed;

use Cyndaron\Model;
use function array_key_exists;

final class Graduation extends Model
{
    public const TABLE = 'geelhoed_graduations';
    public const TABLE_FIELDS = ['sportId', 'name'];

    public int $sportId;
    public string $name;

    private static array $sportCache = [];

    public function getSport(): Sport
    {
        if (!array_key_exists($this->sportId, static::$sportCache))
        {
            static::$sportCache[$this->sportId] = Sport::loadFromDatabase($this->sportId);
        }

        return static::$sportCache[$this->sportId];
    }

    /**
     * @param Sport $sport
     * @return self[]
     */
    public static function fetchAllBySport(Sport $sport): array
    {
        return static::fetchAll(['sportId = ?'], [$sport->id]);
    }
}
