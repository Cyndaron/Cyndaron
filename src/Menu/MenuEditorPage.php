<?php
declare (strict_types = 1);

namespace Cyndaron\Menu;

use Cyndaron\Page;
use Cyndaron\User\User;
use Cyndaron\Widget\Modal;
use Cyndaron\Widget\Toolbar;

require_once __DIR__ . '/../../check.php';

class MenuEditorPage extends Page
{
    public function __construct()
    {
        parent::__construct('Menu-editor');

        $this->showPrePage();
        $this->addScript('/src/Menu/MenuEditorPage.js');

        echo new Toolbar('', '',
            '<button id="mm-create-item"
                data-csrf-token="' . User::getCSRFToken('menu', 'addItem') . '"
                data-toggle="modal" data-target="#mm-edit-item-dialog"
                type="button" class="btn btn-success" data-toggle="modal" data-target="#mm-edit-item-dialog">
                <span class="glyphicon glyphicon-plus"></span> Nieuw menuitem
            </button>'
        );

        $menu = Menu::get();
        include __DIR__ . '/MenuEditorPageTemplate.php';

        echo new Modal('mm-edit-item-dialog', 'Menu-item bewerken',
            '<input type="hidden" id="mm-id" />
            <input type="hidden" id="mm-csrf-token" />

            <div class="form-group row">
                <label for="mm-link" class="col-sm-2 col-form-label">Link:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="mm-link">
                </div>
            </div>
            <div class="form-group row">
                <label for="mm-alias" class="col-sm-2 col-form-label">Alias:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="mm-alias">
                </div>
            </div>
            <div class="form-group row">
                <label for="mm-volgorde" class="col-sm-2 col-form-label">Index:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="mm-volgorde">
                </div>
            </div>
            
            <div class="form-group row">
                <div class="col-sm-12">
                    <input type="checkbox" class="" id="mm-isDropdown" value="1">
                    <label class="form-check-label" for="mm-isDropdown">Dropdown</label>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-12">
                    <input type="checkbox" class="" id="mm-isImage" value="1">
                    <label class="form-check-label" for="mm-isImage">Als afbeelding</label>
                </div>
            </div>',
            '<button id="mm-edit-item-save" type="button" class="btn btn-primary">Opslaan</button>
             <button type="button" class="btn btn-outline-cyndaron" data-dismiss="modal">Annuleren</button>'
        );

        $this->showPostPage();
    }
}