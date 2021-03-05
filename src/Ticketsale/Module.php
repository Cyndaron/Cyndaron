<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;

final class Module implements Routes, Datatypes
{
    public function routes(): array
    {
        return [
            'concert' => ConcertController::class,
            'concert-order' => OrderController::class,
        ];
    }

    public function dataTypes(): array
    {
        return [
            'concert' => Datatype::fromArray([
                'singular' => 'Concert',
                'plural' => 'Concerten',
                'editorPage' => EditorPage::class,
                'editorSavePage' => EditorSavePage::class,
                'pageManagerTab' => Util::class . '::drawPageManagerTab',
            ])
        ];
    }

    public function getList(): array
    {
        return DBConnection::doQueryAndFetchAll('SELECT CONCAT(\'/concert/order/\', id) AS link, CONCAT(\'Concert: \', name) AS name FROM ticketsale_concerts') ?: [];
    }
}
