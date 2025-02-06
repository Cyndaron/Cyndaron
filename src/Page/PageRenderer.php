<?php
declare(strict_types=1);

namespace Cyndaron\Page;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\Error\ErrorPage;
use Cyndaron\User\UserMenu;
use Cyndaron\User\UserSession;
use Cyndaron\View\Template\TemplateRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PageRenderer
{
    public function __construct(
        private readonly ModuleRegistry $registry,
        private readonly TemplateRenderer $templateRenderer,
        private readonly PageBuilder $pageBuilder,
        private readonly MenuRenderer $menuRenderer,
        private readonly UserSession $userSession,
        private readonly Request $request,
        private readonly UserMenu $userMenu,
    ) {
    }

    /**
     * @param array<string, mixed> $vars
     */
    public function render(Page $page, array $vars = []): string
    {
        $this->pageBuilder->updateTemplate($page);
        $userMenuItems = $this->userMenu->getForCurrentSession($this->userSession, $this->registry->userMenuItems);
        $isFrontPage = $this->request->getRequestUri() === '/';
        $page->addTemplateVar('menu', $this->menuRenderer->render($this->userSession, $userMenuItems));
        $page->addTemplateVars($vars);
        $this->pageBuilder->addDefaultTemplateVars($page, $this->userSession, $isFrontPage);

        foreach ($this->registry->pageProcessors as $processor)
        {
            $processor->process($page);
        }

        return $this->templateRenderer->render($page->template, $page->templateVars);
    }

    /**
     * @param array<string, mixed> $vars
     * @param array<string, string> $headers
     */
    public function renderResponse(Pageable $page, array $vars = [], int $status = 200, array $headers = []): Response
    {
        return new Response($this->render($page->toPage(), $vars), $status, $headers);
    }

    public function renderErrorResponse(ErrorPage $errorPage): Response
    {
        return $this->renderResponse($errorPage, status: $errorPage->status, headers: $errorPage->headers);
    }
}
