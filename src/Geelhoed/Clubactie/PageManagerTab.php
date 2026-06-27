<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Clubactie;

use Cyndaron\PageManager\PageManagerTabInterface;
use Cyndaron\Request\QueryBits;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\View\Template\TemplateRenderer;
use function usort;

final class PageManagerTab implements PageManagerTabInterface
{
    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
        private readonly CSRFTokenHandler $tokenHandler,
        private readonly SubscriberRepository $repository
    ) {
    }

    public function render(QueryBits $queryBits): string
    {
        $subscribers = $this->repository->fetchAll();
        usort($subscribers, function(Subscriber $s1, Subscriber $s2)
        {
            $sort1 = $s1->soldTicketsAreVerified <=> $s2->soldTicketsAreVerified;
            return ($sort1 !== 0) ? $sort1 : ($s1->lastName <=> $s2->lastName);
        });
        return $this->templateRenderer->render('Geelhoed/Clubactie/PageManagerTab', [
            'subscribers' => $subscribers,
            'tokenHandler' => $this->tokenHandler,
        ]);
    }
}
