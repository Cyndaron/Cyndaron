<?php
/*
 * Copyright © 2009-2020, Michael Steenbeek
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Reservation;

use Cyndaron\DBConnection;
use Cyndaron\Geelhoed\Hour\Hour;
use Cyndaron\Model;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use DateTimeInterface;

final class Reservation extends Model
{
    public const TABLE = 'geelhoed_reservation';
    public const TABLE_FIELDS = ['hourId', 'date', 'name'];

    public int $hourId;
    public string $name = '';
    public string $date;

    public static function getHoursAndDatesStatistics(): array
    {
        /** @phpstan-ignore-next-line ("Safe" implementation is buggy) */
        $today = new DateTimeImmutable();
        return DBConnection::doQueryAndFetchAll('SELECT `date`,`hourId`, COUNT(*) as count FROM `geelhoed_reservation` WHERE date >= ? GROUP BY `date`,`hourId` ORDER BY `date`,`hourId`', [$today->format('Y-m-d')]) ?: [];
    }

    /**
     * @param Hour $hour
     * @param int $dayRange
     * @return array
     */
    public static function getDatesForHour(Hour $hour, int $dayRange = 15): array
    {
        /** @phpstan-ignore-next-line ("Safe" implementation is buggy) */
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
