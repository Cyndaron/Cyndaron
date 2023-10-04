<?php
/**
 * Copyright © 2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Reservation;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Geelhoed\Hour\Hour;
use Cyndaron\DBAL\Model;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use DateTimeInterface;
use function count;

final class Reservation extends Model
{
    public const TABLE = 'geelhoed_reservation';
    public const TABLE_FIELDS = ['hourId', 'date', 'name'];

    public int $hourId;
    public string $name = '';
    public string $date;

    /**
     * @return array{id: int, hourId: int, date: string, name: string, created: string, modified: string}
     */
    public static function getHoursAndDatesStatistics(): array
    {
        $today = new DateTimeImmutable();
        /** @var array{id: int, hourId: int, date: string, name: string, created: string, modified: string} $ret */
        $ret = DBConnection::getPDO()->doQueryAndFetchAll('SELECT `date`,`hourId`, COUNT(*) as count FROM `geelhoed_reservation` WHERE date >= ? GROUP BY `date`,`hourId` ORDER BY `date`,`hourId`', [$today->format('Y-m-d')]) ?: [];
        return $ret;
    }

    /**
     * @param Hour $hour
     * @param int $dayRange
     * @return list<array{date: DateTimeInterface, leftoverPlaces: int}>
     */
    public static function getDatesForHour(Hour $hour, int $dayRange = 15): array
    {
        $today = new DateTimeImmutable();
        $twoWeeks = $today->add(new DateInterval("P{$dayRange}D"));
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($today, $interval, $twoWeeks);

        $ret = [];
        foreach ($period as $day)
        {
            // Lessons are only given at one specific day a week.
            if ($hour->day !== (int)$day->format('w'))
            {
                continue;
            }

            $key = $day->format('Y-m-d');

            // Skip if there is no more room
            $reservations = self::fetchAll(['hourId = ?', 'date = ?'], [$hour->id, $key]);
            if (count($reservations) >= $hour->capacity)
            {
                continue;
            }

            $ret[] = [
                'date' => $day,
                'leftoverPlaces' => $hour->capacity - count($reservations)
            ];
        }

        return $ret;
    }

    public static function getLeftoverPlacesByHourAndDate(Hour $hour, DateTimeInterface $date): int
    {
        $reservations = self::fetchAll(['hourId = ?', 'date = ?'], [$hour->id, $date->format('Y-m-d')]);
        return $hour->capacity - count($reservations);
    }
}
