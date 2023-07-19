<?php
namespace Cyndaron\Geelhoed;

use Cyndaron\DBAL\Model;
use Cyndaron\Geelhoed\Sport\Sport;

final class Graduation extends Model
{
    public const TABLE = 'geelhoed_graduations';
    public const TABLE_FIELDS = ['sportId', 'name'];

    public int $sportId;
    public string $name;

    public function getSport(): Sport
    {
        /** @var Sport $sport */
        $sport = Sport::fetchById($this->sportId);
        return $sport;
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
