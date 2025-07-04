<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout;

use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\Location\Location;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserSession;
use function assert;

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
        $location = $this->repository->fetchById(Location::class, $this->post->getInt('locationId'));
        assert($location !== null);
        $tryout = $this->repository->fetchOrCreate(Tryout::class, $id);
        $tryout->name = $this->post->getHTML('titel');
        $tryout->location = $location;
        $tryout->start = $this->post->getDateObject('start');
        $tryout->end = $this->post->getDateObject('end');

        try
        {
            $this->repository->save($tryout);
            $this->userSession->addNotification('Toernooi opgeslagen.');
        }
        catch (\PDOException)
        {
            $this->userSession->addNotification('Fout bij opslaan toernooi');
        }

        $this->returnUrl = '/pagemanager/tryout';

        assert($tryout->id !== null);
        return $tryout->id;
    }
}
