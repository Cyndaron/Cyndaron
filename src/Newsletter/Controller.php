<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
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
