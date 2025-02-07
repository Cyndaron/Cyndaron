<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout;

use Cyndaron\DBAL\Connection;
use Cyndaron\Page\Page;
use Cyndaron\Util\Util;
use DateTimeImmutable;

class ScoresPage extends Page
{
    public function __construct(int $code, Connection $connection)
    {
        $this->title = 'Punten opvragen: ' . $code;

        $pointsRecords = $connection->doQueryAndFetchAll(
            'SELECT * FROM geelhoed_tryout_points WHERE code = :code ORDER BY datetime',
            [':code' => $code]
        ) ?: [];

        $rows = [];
        $accTotal = 0;
        foreach ($pointsRecords as $record)
        {
            $accTotal += (int)$record['points'];
            $rows[] = new PointsRow($this->getDate((string)$record['datetime']), (int)$record['points'], $accTotal);
        }

        $this->addTemplateVars([
            'rows' => $rows,
        ]);
        $this->addScript('/src/Geelhoed/Tryout/js/ScoresFormPage.js');
    }

    private function getDate(string|null $datetime): DateTimeImmutable|null
    {
        if ($datetime === null)
        {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat(Util::SQL_DATE_TIME_FORMAT, $datetime);
        if ($date === false)
        {
            return null;
        }

        return $date;
    }
}
