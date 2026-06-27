<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Sport;

use Cyndaron\PageManager\PageManagerTabInterface;
use Cyndaron\Request\QueryBits;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\View\Template\TemplateRenderer;

final class PageManagerTab implements PageManagerTabInterface
{
    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
        private readonly CSRFTokenHandler $tokenHandler,
        private readonly SportRepository $repository
    ) {
    }

    public function render(QueryBits $queryBits): string
    {
        $sports = $this->repository->fetchAll();
        return $this->templateRenderer->render('Geelhoed/Sport/PageManagerTab', [
            'sports' => $sports,
            'tokenEdit' => $this->tokenHandler->get('sport', 'edit'),
        ]);
    }
}
