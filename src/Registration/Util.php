<?php
namespace Cyndaron\Registration;

use Cyndaron\User\User;
use Cyndaron\Widget\Button;
use Cyndaron\Widget\Toolbar;

class Util extends \Cyndaron\Util
{
    public static function drawPageManagerTab()
    {
        echo new Toolbar('', '', (string)new Button('new', '/editor/event', 'Nieuw evenement', 'Nieuw evenement'));

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
                            (<a href="/event/order/<?=$event->id?>">inschrijfpagina</a>,
                            <a href="/event/viewOrders/<?=$event->id?>">overzicht inschrijvingen</a>)
                        </td>
                        <td>
                            <div class="btn-group">
                                <a class="btn btn-outline-cyndaron btn-sm" href="/editor/event/<?=$event->id?>"><span class="glyphicon glyphicon-pencil" title="Bewerk dit evenement"></span></a>
                                <button class="btn btn-danger btn-sm pm-delete" data-type="event" data-id="<?=$event->id;?>" data-csrf-token="<?=User::getCSRFToken('event', 'delete')?>"><span class="glyphicon glyphicon-trash" title="Verwijder dit evenement"></span></button>
                            </div>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    public static function birthYearToCategory(int $birthYear): string
    {
        $age = date('Y') - $birthYear;

        if ($age < 12)
            return 'Niet opgegeven';

        static $ageRanges = [
            [12, 25], [26, 50], [51, 65], [66, 70], [71, 75], [76, 80]
        ];
        foreach ($ageRanges as $ageRange)
        {
            if ($age >= $ageRange[0] && $age <= $ageRange[1])
                return "$ageRange[0] - $ageRange[1]";
        }

        return '81+';
    }
}