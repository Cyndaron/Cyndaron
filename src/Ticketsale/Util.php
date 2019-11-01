<?php
namespace Cyndaron\Ticketsale;

use Cyndaron\DBConnection;
use Cyndaron\User\User;
use Cyndaron\Widget\Button;
use Cyndaron\Widget\Toolbar;

class Util extends \Cyndaron\Util
{
    const MAX_RESERVED_SEATS = 330;

    public static function postcodeQualifiesForFreeDelivery(int $postcode): bool
    {
        if ($postcode >= 4330 && $postcode <= 4399)
            return true;
        else
            return false;
    }

    public static function getLatestConcertId(): ?int
    {
        return DBConnection::doQueryAndFetchOne('SELECT MAX(id) FROM ticketsale_concerts');
    }

    public static function drawPageManagerTab(): void
    {
        echo new Toolbar('', '', (string)new Button('new', '/editor/concert', 'Nieuw concert', 'Nieuw concert'));

        $concerts = Concert::fetchAll();
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
                <?php foreach ($concerts as $concert):?>
                    <tr>
                        <td><?=$concert->id?></td>
                        <td>
                            <?=$concert->name?>
                            (<a href="/concert/order/<?=$concert->id?>">bestelpagina</a>,
                            <a href="/concert/viewOrders/<?=$concert->id?>">overzicht bestellingen</a>)
                        </td>
                        <td>
                            <div class="btn-group">
                                <a class="btn btn-outline-cyndaron btn-sm" href="/editor/concert/<?=$concert->id?>"><span class="glyphicon glyphicon-pencil" title="Bewerk dit concert"></span></a>
                                <button class="btn btn-danger btn-sm pm-delete" data-type="concert" data-id="<?=$concert->id;?>" data-csrf-token="<?=User::getCSRFToken('concert', 'delete')?>"><span class="glyphicon glyphicon-trash" title="Verwijder dit concert"></span></button>
                            </div>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
}