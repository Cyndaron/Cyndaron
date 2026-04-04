<?php
declare(strict_types=1);

namespace Cyndaron\System;

use Cyndaron\CyndaronInfo;
use Cyndaron\Page\Page;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Translation\Translator;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

final class AboutPage
{
    use SystemPageTrait;

    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly Translator $t
    ) {
    }

    #[RouteAttribute('about', RequestMethod::GET, UserLevel::ADMIN)]
    public function show(): Response
    {
        $page = new Page();
        $page->template = 'System/About';
        $this->setCommonVariables($page, 'about');
        $page->templateVars += [
            'productName' => CyndaronInfo::PRODUCT_NAME,
            'productVersion' => CyndaronInfo::PRODUCT_VERSION,
            'productCodename' => CyndaronInfo::PRODUCT_CODENAME,
            'engineVersion' => CyndaronInfo::ENGINE_VERSION,
        ];
        return $this->pageRenderer->renderResponse($page);
    }
}
