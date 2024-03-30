<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\TicketType;

use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;

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
            User::addNotification('Kaarttype opgeslagen.');
        }
        else
        {
            User::addNotification('Fout bij opslaan kaarttype');
        }

        $this->returnUrl = '/pagemanager/concert';

        return -1;
    }
}