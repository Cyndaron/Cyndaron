<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\TicketType;

use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;
use Cyndaron\User\UserSession;

final class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    public function __construct(
        private readonly RequestParameters $post,
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
            UserSession::addNotification('Kaarttype opgeslagen.');
        }
        else
        {
            UserSession::addNotification('Fout bij opslaan kaarttype');
        }

        $this->returnUrl = '/pagemanager/concert';

        return -1;
    }
}
