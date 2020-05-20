<?php
namespace Cyndaron\FriendlyUrl;

use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;
use Cyndaron\PageManager\PageManagerPage;

class Module implements Datatypes, Routes
{
    /**
     * @inheritDoc
     */
    public function dataTypes(): array
    {
        return [
            'friendlyurl' => Datatype::fromArray([
                'singular' => 'Friendly URL',
                'plural' => 'Friendly URL\'s',
                'pageManagerTab' => PageManagerPage::class . '::showFriendlyUrls',
            ]),
        ];
    }

    /**
     * @inheritDoc
     */
    public function routes(): array
    {
        return [
            'friendlyurl' => FriendlyUrlController::class,
        ];
    }
}
