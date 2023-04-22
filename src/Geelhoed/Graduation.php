<?php
namespace Cyndaron\Geelhoed;

use Cyndaron\DBAL\Model;
use function array_key_exists;

final class Graduation extends Model
{
    public const TABLE = 'geelhoed_graduations';
    public const TABLE_FIELDS = ['sportId', 'name'];

    public int $sportId;
    public string $name;

    public function getSport(): Sport
    {
        return Sport::fetchById($this->sportId);
    }

    /**
     * @param Sport $sport
     * @return self[]
     */
    public static function fetchAllBySport(Sport $sport): array
    {
        return self::fetchAll(['sportId = ?'], [$sport->id]);
    }
}
