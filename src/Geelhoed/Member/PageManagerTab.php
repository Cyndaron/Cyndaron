<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Member;

use Cyndaron\Geelhoed\Graduation\GraduationRepository;
use Cyndaron\Geelhoed\Location\LocationRepository;
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
        private readonly LocationRepository $locationRepository,
        private readonly SportRepository $sportRepository,
        private readonly GraduationRepository $graduationRepository
    ) {
    }

    public function render(QueryBits $queryBits): string
    {
        return $this->templateRenderer->render('Geelhoed/Member/PageManagerTab', [
            'locations' => $this->locationRepository->fetchAll(afterWhere: 'ORDER BY city, street'),
            'tokenDelete' => $this->tokenHandler->get('member', 'delete'),
            'tokenSave' => $this->tokenHandler->get('member', 'save'),
            'tokenRemoveGraduation' => $this->tokenHandler->get('member', 'removeGraduation'),
            'sports' => $this->sportRepository->fetchAll(),
            'locationRepository' => $this->locationRepository,
            'graduations' => $this->graduationRepository->fetchAll(),
        ]);
    }
}
