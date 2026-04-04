<?php
declare(strict_types=1);

namespace Cyndaron\System;

use Cyndaron\CyndaronInfo;
use Cyndaron\Page\Page;

trait SystemPageTrait
{
    public function setCommonVariables(Page $page, string $currentPage): void
    {
        $page->title = $this->t->get('Systeembeheer');

        $page->templateVars['currentPage'] = $currentPage;
        $page->templateVars['pageTabs'] = [
            'config' => 'Configuratie',
            'phpinfo' => 'PHP-info',
            'checks' => 'Checks',
            'about' => 'Over ' . CyndaronInfo::PRODUCT_NAME,
        ];
    }
}
