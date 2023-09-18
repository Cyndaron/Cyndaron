<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Linkable;
use Cyndaron\Module\Routes;
use Cyndaron\Module\Templated;
use Cyndaron\Module\TemplateRoot;
use Cyndaron\Ticketsale\Order\OrderController;
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
            ])
        ];
    }

    public function getList(): array
    {
        /** @var list<array{name: string, link: string}> $list */
        $list = DBConnection::getPDO()->doQueryAndFetchAll('SELECT CONCAT(\'/concert/order/\', id) AS link, CONCAT(\'Concert: \', name) AS name FROM ticketsale_concerts');
        return array_map(static function (array $item)
        {
            return Link::fromArray($item);
        }, $list);
    }

    public function getTemplateRoot(): TemplateRoot
    {
        return new TemplateRoot('Ticketsale', __DIR__);
    }
}
