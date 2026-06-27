<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Geelhoed\Contest\Model\ContestDateRepository;
use Cyndaron\Geelhoed\Contest\Model\ContestRepository;
use Cyndaron\Geelhoed\Sport\SportRepository;
use Cyndaron\PageManager\PageManagerTabInterface;
use Cyndaron\Request\QueryBits;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\View\Template\TemplateRenderer;

final class PageManagerTab implements PageManagerTabInterface
{
    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
        private readonly CSRFTokenHandler $tokenHandler,
        private readonly ContestRepository $contestRepository,
        private readonly ContestDateRepository $contestDateRepository,
        private readonly SportRepository $sportRepository
    ) {
    }

    public function render(QueryBits $queryBits): string
    {
        $contests = $this->contestRepository->fetchAll([], [], 'ORDER BY registrationDeadline DESC');
        return $this->templateRenderer->render('Geelhoed/Contest/Page/PageManagerTab', [
            'contests' => $contests,
            'contestRepository' => $this->contestRepository,
            'contestDateRepository' => $this->contestDateRepository,
            'tokenEdit' => $this->tokenHandler->get('contest', 'edit'),
            'tokenDelete' => $this->tokenHandler->get('contest', 'delete'),
            'sports' => $this->sportRepository->fetchAllForSelect(),
        ]);
    }
}
