<?php
declare(strict_types=1);

namespace Cyndaron\Location;

use Cyndaron\DBAL\Repository\GenericRepository;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    public const TYPE = 'location';
    public const HAS_TITLE = true;

    public string $template = '';

    public function __construct(
        private readonly GenericRepository $genericRepository
    ) {
    }

    public function prepare(): void
    {
        if ($this->id)
        {
            $this->model = $this->genericRepository->fetchById(Location::class, $this->id);
        }
    }
}
