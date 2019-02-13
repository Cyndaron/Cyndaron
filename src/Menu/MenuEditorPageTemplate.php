<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Link</th>
            <th>Alias</th>
            <th>Opties</th>
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
                <div class="checkbox">
                    <label>
                        <input type="checkbox" class="isDropdown" data-id="<?=$menuItem['volgorde']?>" data-csrf-token="<?=Cyndaron\User\User::getCSRFToken('menu', 'setDropdown')?>" <?=$menuItem['isDropdown'] ? 'checked' : ''?> /> Dropdown
                    </label>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" class="isImage" data-id="<?=$menuItem['volgorde']?>" data-csrf-token="<?=Cyndaron\User\User::getCSRFToken('menu', 'setImage')?>" <?=$menuItem['isImage'] ? 'checked' : ''?>/> Afbeelding
                    </label>
                </div>
            </td>
            <td>
                <button class="removeItem btn btn-danger" data-id="<?=$menuItem['volgorde']?>" data-csrf-token="<?=Cyndaron\User\User::getCSRFToken('menu', 'removeItem')?>"><span class="glyphicon glyphicon-trash"></span> Verwijderen</button>
            </td>
        </tr>
<?php endforeach;?>
    </tbody>
</table>
