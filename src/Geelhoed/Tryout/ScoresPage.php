<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Util\Util;
use Cyndaron\View\Page;
use DateTimeImmutable;

class ScoresPage extends Page
{
    public function __construct(int $code)
    {
        parent::__construct('Punten opvragen: ' . $code);

        $pointsRecords = DBConnection::doQueryAndFetchAll(
            'SELECT * FROM geelhoed_tryout_points WHERE code = :code ORDER BY datetime',
            [':code' => $code]
        ) ?: [];

        $rows = [];
        $accTotal = 0;
        foreach ($pointsRecords as $record)
        {
            $accTotal += $record['points'];
            $rows[] = new PointsRow($this->getDate($record['datetime']), (int)$record['points'], $accTotal);
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

        $date = DateTimeImmutable::createFromFormat(Util::SQL_DATE_FORMAT, $datetime);
        if ($date === false)
        {
            return null;
        }

        return $date;
    }
}
