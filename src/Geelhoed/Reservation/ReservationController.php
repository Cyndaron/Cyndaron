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

use Cyndaron\Error\ErrorPageResponse;
use Cyndaron\Geelhoed\Hour\Hour;
use Cyndaron\Page;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\Controller;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use function is_array;
use function array_walk;
use function filter_var;
use function trim;
use function array_filter;
use function count;

final class ReservationController extends Controller
{
    protected array $getRoutes = [
        'lesson' => ['level' => UserLevel::ADMIN, 'function' => 'lesson'],
        'overview' => ['level' => UserLevel::ADMIN, 'function' => 'overview'],
        'step-1' => ['level' => UserLevel::ANONYMOUS, 'function' => 'step1'],
    ];

    protected array $postRoutes = [
        'step-2' => ['level' => UserLevel::ANONYMOUS, 'function' => 'step2'],
        'step-3' => ['level' => UserLevel::ANONYMOUS, 'function' => 'step3'],
        'step-last' => ['level' => UserLevel::ANONYMOUS, 'function' => 'stepLast'],
    ];

    public function overview(): Response
    {
        $page = new OverviewPage();
        return new Response($page->render());
    }

    public function lesson(QueryBits $queryBits): Response
    {
        $hourId = $queryBits->getInt(2);
        $hour = Hour::loadFromDatabase($hourId);
        if ($hour === null)
        {
            return new ErrorPageResponse('Fout', 'Lesuur bestaat niet!', Response::HTTP_NOT_FOUND);
        }

        $dateString = $queryBits->getString(3);
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $dateString);
        if ($date === false)
        {
            return new ErrorPageResponse('Fout', 'Datum klopt niet!', Response::HTTP_BAD_REQUEST);
        }

        $page = new LessonPage($hour, $date);
        return new Response($page->render());
    }

    public function step1(): Response
    {
        $page = new ReserveStep1Page();
        return new Response($page->render());
    }

    public function step2(RequestParameters $post): Response
    {
        $hourId = $post->getInt('hourId');
        $hour = Hour::loadFromDatabase($hourId);
        if ($hour === null)
        {
            return new ErrorPageResponse('Fout', 'Lesuur bestaat niet!', Response::HTTP_NOT_FOUND);
        }

        $page = new ReserveStep2Page($hour);
        return new Response($page->render());
    }

    public function step3(RequestParameters $post): Response
    {
        $hourId = $post->getInt('hourId');
        $hour = Hour::loadFromDatabase($hourId);
        if ($hour === null)
        {
            return new ErrorPageResponse('Fout', 'Lesuur bestaat niet!', Response::HTTP_NOT_FOUND);
        }

        $dateString = $post->getDate('date');
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $dateString);
        if ($date === false)
        {
            return new ErrorPageResponse('Fout', 'Datum klopt niet!', Response::HTTP_BAD_REQUEST);
        }

        $page = new ReserveStep3Page($hour, $date);
        return new Response($page->render());
    }

    public function stepLast(RequestParameters $post): Response
    {
        $hourId = $post->getInt('hourId');
        $hour = Hour::loadFromDatabase($hourId);
        if ($hour === null)
        {
            return new ErrorPageResponse('Fout', 'Lesuur bestaat niet!', Response::HTTP_NOT_FOUND);
        }

        $dateString = $post->getDate('date');
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $dateString);
        if ($date === false)
        {
            return new ErrorPageResponse('Fout', 'Datum klopt niet!', Response::HTTP_BAD_REQUEST);
        }

        $names = $post->get('name');
        if (!is_array($names))
        {
            return new ErrorPageResponse('Fout', 'Geen namen opgegeven a!', Response::HTTP_BAD_REQUEST);
        }

        array_walk($names, static function(&$name)
        {
            $name = filter_var($name, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_NO_ENCODE_QUOTES) ?: '';
            $name = trim($name);
        });
        $names = array_filter($names, static function(string $name)
        {
            return $name !== '';
        });

        $numNames = count($names);
        if ($numNames === 0)
        {
            return new ErrorPageResponse('Fout', 'Geen namen opgegeven!', Response::HTTP_BAD_REQUEST);
        }

        $leftoverPlaces = Reservation::getLeftoverPlacesByHourAndDate($hour, $date);
        $savedNames = 0;
        foreach ($names as $name)
        {
            if ($leftoverPlaces <= 0)
            {
                break;
            }

            $reservation = new Reservation();
            $reservation->hourId = $hourId;
            $reservation->date = $date->format('Y-m-d');
            $reservation->name = $name;
            if ($reservation->save())
            {
                $savedNames++;
                $leftoverPlaces--;
            }
        }

        if ($savedNames < $numNames)
        {
            return new ErrorPageResponse('Fout', "Kon niet alle namen opslaan! {$savedNames} van de {$numNames} namen zijn opgeslagen. Waarschijnlijk zit de les vol.", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        User::addNotification('De reservering is succesvol verlopen.');
        return new RedirectResponse('/');
    }
}
