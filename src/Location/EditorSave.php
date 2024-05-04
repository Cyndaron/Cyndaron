<?php
declare(strict_types=1);

namespace Cyndaron\Location;

use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserSession;

class EditorSave extends \Cyndaron\Editor\EditorSave
{
    public function __construct(
        private readonly RequestParameters $post,
    ) {
    }

    public function save(?int $id): int
    {
        $location = new Location($id);
        $location->loadIfIdIsSet();

        $location->name = $this->post->getSimpleString('name');
        $location->street = $this->post->getSimpleString('street');
        $location->houseNumber = $this->post->getSimpleString('houseNumber');
        $location->postalCode = $this->post->getSimpleString('postalCode');
        $location->city = $this->post->getSimpleString('city');

        if ($location->save())
        {
            $newId = (int)$location->id;
            UserSession::addNotification('Locatie opgeslagen.');
            $this->returnUrl = '/locaties/details/' . $newId;
            return $newId;
        }
        else
        {
            UserSession::addNotification('Fout bij opslaan locatie');
            $this->returnUrl = '/pagemanager/locations';
            return -1;
        }
    }
}
