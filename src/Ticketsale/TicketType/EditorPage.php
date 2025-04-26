<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\TicketType;

use Cyndaron\Request\QueryBits;

final class EditorPage extends \Cyndaron\Editor\EditorPage
{
    public const TYPE = 'ticketType';
    public const HAS_TITLE = false;
    public const SAVE_URL = '/editor/ticketType/%s';

    public string $template = '';

    public function __construct(
        private readonly QueryBits $queryBits,
        private readonly TicketTypeRepository $ticketTypeRepository,
    ) {
    }

    public function prepare(): void
    {
        if ($this->id)
        {
            $this->model = $this->ticketTypeRepository->fetchById($this->id);
            $concertId = $this->model?->concertId;
        }
        else
        {
            $concertId = $this->queryBits->getInt(3);
        }

        $this->addTemplateVar('concertId', $concertId);
    }
}
