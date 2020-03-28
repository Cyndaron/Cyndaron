<?php
namespace Cyndaron\Geelhoed;

use Cyndaron\Model;

class Graduation extends Model
{
    public const TABLE = 'geelhoed_graduations';
    public const TABLE_FIELDS = ['sportId', 'name'];

    public string $sportId;
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
}