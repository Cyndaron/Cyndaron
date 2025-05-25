<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout;

use Cyndaron\Geelhoed\Location\LocationRepository;
use function assert;
use function asort;

final class EditorPage extends \Cyndaron\Editor\EditorPage
{
    public const TYPE = 'tryout';
    public const HAS_CATEGORY = false;
    public const SAVE_URL = '/editor/tryout/%s';

    public string $template = '';

    public function __construct(
        private readonly TryoutRepository $tryoutRepository,
        private readonly LocationRepository $locationRepository,
    ) {

    }

    public function prepare(): void
    {
        if ($this->id)
        {
            $tryout = $this->tryoutRepository->fetchById($this->id);
            assert($tryout !== null);
            $this->model = $tryout;
            $this->content = $this->model->description ?? '';
            $this->contentTitle = $this->model->name ?? '';
        }

        $locations = [];
        foreach ($this->locationRepository->fetchAll() as $location)
        {
            $locations[$location->id] = $location->getName();
        }
        asort($locations);
        $this->addTemplateVar('locations', $locations);
    }
}
