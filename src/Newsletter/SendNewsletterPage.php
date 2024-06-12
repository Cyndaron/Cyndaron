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

class SendNewsletterPage extends Page
{
    public function __construct(CSRFTokenHandler $tokenHandler)
    {
        parent::__construct('Nieuwsbrief versturen');
        $this->addTemplateVars([
            'csrfToken' => $tokenHandler->get('newsletter', 'send'),
        ]);
        $this->addScript('/vendor/ckeditor/ckeditor/ckeditor.js');
        $this->addScript('/js/editor.js');
        $this->addScript('/src/Newsletter/js/SendNewsletterPage.js');
        $this->addCss('/src/Newsletter/css/SendNewsletterPage.css');
    }
}
