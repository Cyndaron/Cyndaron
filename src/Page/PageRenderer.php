<?php
declare(strict_types=1);

namespace Cyndaron\Page;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\Error\ErrorPage;
use Cyndaron\User\User;
use Cyndaron\User\UserMenu;
use Cyndaron\View\Renderer\TextRenderer;
use Cyndaron\View\Template\TemplateRenderer;
use Symfony\Component\HttpFoundation\Response;

final class PageRenderer
{
    public function __construct(
        private readonly ModuleRegistry $registry,
        private readonly TemplateRenderer $templateRenderer,
        private readonly TextRenderer $textRenderer,
        private readonly User|null $currentUser
    )
    {
    }

    /**
     * @param array<string, mixed> $vars
     */
    public function render(Page $page, array $vars = []): string
    {
        $userMenu = UserMenu::getForUser($this->currentUser, $this->registry->userMenuItems);
        return $page->render($this->templateRenderer, $this->textRenderer, $userMenu, $vars);
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
