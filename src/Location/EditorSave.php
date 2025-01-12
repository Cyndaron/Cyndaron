<?php
declare(strict_types=1);

namespace Cyndaron\Location;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserSession;

class EditorSave extends \Cyndaron\Editor\EditorSave
{
    public function __construct(
        private readonly RequestParameters $post,
        private readonly UserSession       $userSession,
        private readonly GenericRepository $repository,
    ) {
    }

    public function save(?int $id): int
    {
        $location = $this->repository->fetchOrCreate(Location::class, $id);
        $location->name = $this->post->getSimpleString('name');
        $location->street = $this->post->getSimpleString('street');
        $location->houseNumber = $this->post->getSimpleString('houseNumber');
        $location->postalCode = $this->post->getSimpleString('postalCode');
        $location->city = $this->post->getSimpleString('city');

        try
        {
            $this->repository->save($location);
            $newId = (int)$location->id;
            $this->userSession->addNotification('Locatie opgeslagen.');
            $this->returnUrl = '/locaties/details/' . $newId;
            return $newId;
        }
        catch (\PDOException)
        {
            $this->userSession->addNotification('Fout bij opslaan locatie');
            $this->returnUrl = '/pagemanager/locations';
            return -1;
        }
    }
}
