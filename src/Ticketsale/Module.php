<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale;

use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;
use Cyndaron\Module\Templated;
use Cyndaron\Module\TemplateRoot;
use Cyndaron\Ticketsale\Concert\Concert;
use Cyndaron\Ticketsale\Concert\ConcertController;
use Cyndaron\Ticketsale\Concert\EditorPage;
use Cyndaron\Ticketsale\Concert\EditorSave;
use Cyndaron\Ticketsale\Order\OrderController;
use Cyndaron\Ticketsale\TicketType\EditorPage as TicketTypeEditorPage;
use Cyndaron\Ticketsale\TicketType\EditorSave as TicketTypeEditorSave;
use Cyndaron\Ticketsale\TicketType\TicketType;
use Cyndaron\Url\Url;
use Cyndaron\Util\Link;

final class Module implements Routes, Datatypes, Templated
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
            'concert' => new Datatype(
                singular: 'Concert',
                plural: 'Concerten',
                editorPage: EditorPage::class,
                editorSave: EditorSave::class,
                pageManagerTab: Util::drawPageManagerTab(...),
                class: Concert::class,
                modelToUrl: function(Concert $concert)
                { return new Url("/concert/{$concert->id}"); }
            ),
            'ticketType' => new Datatype(
                editorPage: TicketTypeEditorPage::class,
                editorSave: TicketTypeEditorSave::class,
                class: TicketType::class
            ),
        ];
    }

    public function getTemplateRoot(): TemplateRoot
    {
        return new TemplateRoot('Ticketsale', __DIR__);
    }
}
