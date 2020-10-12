<?php
/*
 * Copyright © 2009-2017, Michael Steenbeek
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

namespace Cyndaron\Newsletter;

use Cyndaron\Page;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

class Controller extends \Cyndaron\Routing\Controller
{
    protected array $getRoutes = [
        'viewSubscribers' => ['level' => UserLevel::ADMIN, 'function' => 'viewSubscribers']
    ];

    protected array $postRoutes = [
        'subscribe' => ['level' => UserLevel::ANONYMOUS, 'function' => 'subscribe']
    ];

    protected function viewSubscribers(): Response
    {
        $page = new ViewSubscribersPage();
        return new Response($page->render());
    }

    protected function subscribe(RequestParameters $post): Response
    {
        $antiSpam = $post->getUnfilteredString('antispam');
        if ($antiSpam !== '')
        {
            $page = new Page('Inschrijving nieuwsbrief', 'Er is iets misgegaan bij het invullen van het formulier.');
            return new Response($page->render(), Response::HTTP_BAD_REQUEST);
        }

        $name = $post->getSimpleString('name');
        $email = $post->getEmail('email');

        $existing = Subscriber::fetch(['email = ?'], [$email]);
        if ($existing !== null)
        {
            $message = 'U was al ingeschreven voor de nieuwsbrief.';
        }
        else
        {
            $subscription = new Subscriber();
            $subscription->name = $name;
            $subscription->email = $email;
            $subscription->save();

            $message = 'U bent ingeschreven voor de nieuwsbrief.';
        }

        $page = new Page('Inschrijving nieuwsbrief', $message);
        return new Response($page->render());
    }
}
