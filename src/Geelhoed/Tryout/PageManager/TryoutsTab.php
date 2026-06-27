<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout\PageManager;

use Cyndaron\Geelhoed\Tryout\TryoutRepository;
use Cyndaron\PageManager\PageManagerTabInterface;
use Cyndaron\Request\QueryBits;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\View\Template\TemplateRenderer;

final class TryoutsTab implements PageManagerTabInterface
{
    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
        private readonly CSRFTokenHandler $tokenHandler,
        private readonly TryoutRepository $repository
    ) {
    }

    public function render(QueryBits $queryBits): string
    {
        $csrfTokenCreatePhotoalbums = $this->tokenHandler->get('tryout', 'create-photoalbums');
        $tryouts = $this->repository->fetchAll();
        return $this->templateRenderer->render('Geelhoed/Tryout/PageManagerTab', [
            'tryouts' => $tryouts,
            'csrfTokenCreatePhotoalbums' => $csrfTokenCreatePhotoalbums,
        ]);
    }
}
