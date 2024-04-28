<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Newsletter;

use Cyndaron\Page\Page;
use Cyndaron\User\UserSession;

class ViewSubscribersPage extends Page
{
    public function __construct()
    {
        parent::__construct('Abonnees nieuwsbrief');

        $this->addTemplateVars([
            'subscribers' => Subscriber::fetchAll([], [], 'ORDER BY name'),
            'csrfTokenUnsubscribe' => UserSession::getCSRFToken('newsletter', 'unsubscribe'),
            'csrfTokenDelete' => UserSession::getCSRFToken('newsletter', 'delete'),
        ]);
    }
}
