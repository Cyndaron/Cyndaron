<?php
use Cyndaron\User\User;
use Cyndaron\Util;
?>
<table id="mm-menutable" class="table table-striped table-bordered"
    data-edit-csrf-token="<?=User::getCSRFToken('menu', 'editItem')?>"
    data-delete-csrf-token="<?=User::getCSRFToken('menu', 'deleteItem')?>"
>
    <thead>
        <tr>
            <th>ID</th>
            <th>Link</th>
            <th>Alias</th>
            <th>Dropdown</th>
            <th>Afbeelding</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
<?php foreach($menu as $menuItem):?>
        <tr>
            <td>
                <?=$menuItem['volgorde']?>
            </td>
            <td>
                <?=$menuItem['link']?>
            </td>
            <td>
                <?=$menuItem['alias']?>
            </td>
            <td>
                <?=Util::boolToText($menuItem['isDropdown']);?>
            </td>
            <td>
                <?=Util::boolToText($menuItem['isImage']);?>
            </td>
            <td>
                <div class="btn-group">
                    <button class="mm-edit-item btn btn-outline-cyndaron"
                            data-id="<?=$menuItem['volgorde']?>"
                            data-toggle="modal"
                            data-target="#mm-edit-item-dialog"
                            data-volgorde="<?=$menuItem['volgorde']?>"
                            data-link="<?=$menuItem['link']?>"
                            data-alias="<?=$menuItem['alias']?>"
                            data-isDropdown="<?=$menuItem['isDropdown']?>"
                            data-isImage="<?=$menuItem['isImage']?>"
                    ><span class="glyphicon glyphicon-pencil"></span></button>
                    <button class="mm-delete-item btn btn-danger" data-id="<?=$menuItem['volgorde']?>"><span class="glyphicon glyphicon-trash"></span></button>
                </div>
            </td>
        </tr>
<?php endforeach;?>
    </tbody>
</table>
