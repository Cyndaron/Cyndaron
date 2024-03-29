<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale;

use Cyndaron\DBAL\Connection;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Linkable;
use Cyndaron\Module\Routes;
use Cyndaron\Module\Templated;
use Cyndaron\Module\TemplateRoot;
use Cyndaron\Ticketsale\Concert\ConcertController;
use Cyndaron\Ticketsale\Concert\EditorPage;
use Cyndaron\Ticketsale\Concert\EditorSavePage;
use Cyndaron\Ticketsale\Order\OrderController;
use Cyndaron\Ticketsale\TicketType\EditorPage as TicketTypeEditorPage;
use Cyndaron\Ticketsale\TicketType\EditorSavePage as TicketTypeEditorSavePage;
use Cyndaron\Util\Link;
use function array_map;

final class Module implements Routes, Datatypes, Templated, Linkable
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
            ]),
            'ticketType' => Datatype::fromArray([
                'editorPage' => TicketTypeEditorPage::class,
                'editorSavePage' => TicketTypeEditorSavePage::class,
            ]),
        ];
    }

    public function getList(Connection $connection): array
    {
        /** @var list<array{name: string, link: string}> $list */
        $list = $connection->doQueryAndFetchAll('SELECT CONCAT(\'/concert/order/\', id) AS link, CONCAT(\'Concert: \', name) AS name FROM ticketsale_concerts');
        return array_map(static function(array $item)
        {
            return Link::fromArray($item);
        }, $list);
    }

    public function getTemplateRoot(): TemplateRoot
    {
        return new TemplateRoot('Ticketsale', __DIR__);
    }
}
