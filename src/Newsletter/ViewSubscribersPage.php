<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Newsletter;

use Cyndaron\Page\Page;
use Cyndaron\User\CSRFTokenHandler;

class ViewSubscribersPage extends Page
{
    public function __construct(CSRFTokenHandler $tokenHandler)
    {
        $this->title = 'Abonnees nieuwsbrief';

        $this->addTemplateVars([
            'subscribers' => Subscriber::fetchAll([], [], 'ORDER BY name'),
            'csrfTokenUnsubscribe' => $tokenHandler->get('newsletter', 'unsubscribe'),
            'csrfTokenDelete' => $tokenHandler->get('newsletter', 'delete'),
        ]);
    }
}
