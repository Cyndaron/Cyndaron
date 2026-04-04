<?php
declare(strict_types=1);

namespace Cyndaron\System;

use Cyndaron\Page\Page;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Translation\Translator;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;
use function ob_get_clean;
use function Safe\ob_start;
use function Safe\phpinfo;
use function Safe\preg_match;
use function Safe\preg_replace;
use function strtr;
use function assert;
use function is_string;

final class PHPInfoPage
{
    use SystemPageTrait;

    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly Translator $t
    ) {
    }

    #[RouteAttribute('phpinfo', RequestMethod::GET, UserLevel::ADMIN)]
    public function show(): Response
    {
        $page = new Page();
        $page->template = 'System/Phpinfo';
        $this->setCommonVariables($page, 'phpinfo');

        $page->addTemplateVar('phpinfo', $this->getPHPInfo());

        return $this->pageRenderer->renderResponse($page);
    }

    public function getPHPInfo(): string
    {
        // Prevent phpinfo() from writing directly to the screen (we want to change the output first)
        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();
        if ($phpinfo === false)
        {
            throw new \Exception('Error retrieving PHPinfo!');
        }

        // We only want the innerhtml of the body.
        preg_match("/<body(.*?)>(.*?)<\\/body>/si", $phpinfo, $match);
        $phpInfoText = $match[2] ?? '';
        $phpinfo = strtr($phpInfoText, [
            // Remove centering
            '<div class="center"' => '<div',
            // Enhance table layout
            '<table>' => '<table class="table">',
        ]);
        // Strip links (and with it, logos)
        $phpinfo = preg_replace('/<a href(.*?)>(.*?)<\/a>/', '', $phpinfo);
        // Old, dirty tags that contain inline style attributes as well (which we don't want).
        $phpinfo = preg_replace('/<font(.*?)>/', '', $phpinfo);
        $phpinfo = preg_replace('/<\/font(.*?)>/', '', $phpinfo);
        assert(is_string($phpinfo));

        return $phpinfo;
    }
}
