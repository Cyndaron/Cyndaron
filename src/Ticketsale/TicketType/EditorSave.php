<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\TicketType;

use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserSession;

final class EditorSave extends \Cyndaron\Editor\EditorSave
{
    public function __construct(
        private readonly RequestParameters $post,
        private readonly UserSession       $userSession,
        private readonly GenericRepository $repository,
    ) {
    }

    public function save(int|null $id): int
    {
        $ticketType = $this->repository->fetchOrCreate(TicketType::class, $id);

        if ($id === null)
        {
            $ticketType->concertId = $this->post->getInt('concertId');
        }

        $ticketType->name = $this->post->getHTML('name');
        $ticketType->price = $this->post->getFloat('price');

        try
        {
            $this->repository->save($ticketType);
            $this->userSession->addNotification('Kaarttype opgeslagen.');
        }
        catch (\PDOException)
        {
            $this->userSession->addNotification('Fout bij opslaan kaarttype');
        }

        $this->returnUrl = '/pagemanager/concert';

        return -1;
    }
}
