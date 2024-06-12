<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\TicketType;

use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserSession;

final class EditorSave extends \Cyndaron\Editor\EditorSave
{
    public function __construct(
        private readonly RequestParameters $post,
        private readonly UserSession $userSession
    ) {
    }

    public function save(int|null $id): int
    {
        $ticketType = new TicketType($id);
        $ticketType->loadIfIdIsSet();

        if ($id === null)
        {
            $ticketType->concertId = $this->post->getInt('concertId');
        }

        $ticketType->name = $this->post->getHTML('name');
        $ticketType->price = $this->post->getFloat('price');

        if ($ticketType->save())
        {
            $this->userSession->addNotification('Kaarttype opgeslagen.');
        }
        else
        {
            $this->userSession->addNotification('Fout bij opslaan kaarttype');
        }

        $this->returnUrl = '/pagemanager/concert';

        return -1;
    }
}
