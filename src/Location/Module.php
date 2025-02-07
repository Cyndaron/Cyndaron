<?php
declare(strict_types=1);

namespace Cyndaron\Location;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\UrlProvider;

final class Module implements Datatypes, UrlProvider
{
    public function dataTypes(): array
    {
        return [
            'location' => new Datatype(
                singular: 'Locatie',
                plural: 'Locaties',
                editorPage: EditorPage::class,
                editorSave: EditorSave::class,
                pageManagerTab: PageManagerTabs::locationsTab(...),
            ),
        ];
    }

    public function nameFromUrl(GenericRepository $genericRepository, array $linkParts): string|null
    {
        if ($linkParts[0] === 'location' && $linkParts[1] === 'view')
        {
            $location = $genericRepository->fetchById(Location::class, (int)$linkParts[2]);
            if ($location === null)
            {
                return null;
            }
            return $location->getName();
        }

        return null;
    }
}
