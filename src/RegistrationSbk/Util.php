<?php
namespace Cyndaron\RegistrationSbk;

use Cyndaron\User\User;
use Cyndaron\Widget\Button;
use Cyndaron\Widget\Toolbar;

class Util extends \Cyndaron\Util
{
    /** @noinspection PhpUnused */
    public static function drawPageManagerTab()
    {
        echo new Toolbar('', '', (string)new Button('new', '/editor/eventSbk', 'Nieuw evenement', 'Nieuw evenement'));

        $events = Event::fetchAll();
        ?>

        <table class="table table-striped table-bordered pm-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Naam</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event):?>
                    <tr>
                        <td><?=$event->id?></td>
                        <td>
                            <?=$event->name?>
                            (<a href="/eventSbk/register/<?=$event->id?>">inschrijfpagina</a>,
                            <a href="/eventSbk/viewRegistrations/<?=$event->id?>">overzicht inschrijvingen</a>)
                        </td>
                        <td>
                            <div class="btn-group">
                                <a class="btn btn-outline-cyndaron btn-sm" href="/editor/eventSbk/<?=$event->id?>"><span class="glyphicon glyphicon-pencil" title="Bewerk dit evenement"></span></a>
                                <button class="btn btn-danger btn-sm pm-delete" data-type="eventSbk" data-id="<?=$event->id;?>" data-csrf-token="<?=User::getCSRFToken('eventSbk', 'delete')?>"><span class="glyphicon glyphicon-trash" title="Verwijder dit evenement"></span></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
}