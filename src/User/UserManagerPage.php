<?php


namespace Cyndaron\User;

use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\Widget\Modal;
use Cyndaron\Widget\Toolbar;

require_once __DIR__ . '/../../check.php';

class UserManagerPage extends Page
{
    const USER_LEVEL_DESCRIPTIONS = [
        'Niet ingelogd',
        'Normale gebruiker',
        'Gereserveerd',
        'Gereserveerd',
        'Beheerder',
    ];

    public function __construct()
    {
        parent::__construct('Gebruikersbeheer');
        $this->addScript('/src/User/UserManagerPage.js');
        parent::showPrePage();

        $users = DBConnection::doQueryAndFetchAll('SELECT * FROM users ORDER BY username');

        echo new Toolbar('', '', '
        <button id="um-create-user"
                data-csrf-token="' . User::getCSRFToken('user', 'add') . '"
                type="button" class="btn btn-success" data-toggle="modal" data-target="#um-edit-user-dialog">
            <span class="glyphicon glyphicon-plus"></span> Nieuwe gebruiker toevoegen
        </button>
        ')
        ?>
        <table
            id="um-usertable"
            class="table table-bordered table-striped"
            data-edit-csrf-token="<?=User::getCSRFToken('user', 'edit')?>"
            data-resetpassword-csrf-token="<?=User::getCSRFToken('user', 'resetpassword')?>"
            data-delete-csrf-token="<?=User::getCSRFToken('user', 'delete')?>">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Gebruikersnaam</th>
                    <th>E-mailadres</th>
                    <th>Niveau</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?=$user['id']?></td>
                    <td><?=$user['username']?></td>
                    <td><?=$user['email']?></td>
                    <td><?=self::USER_LEVEL_DESCRIPTIONS[$user['level']]?></td>
                    <td>
                        <div class="btn-group">
                            <button class="um-edit-user btn btn-sm btn-outline-cyndaron"
                                    data-toggle="modal" data-target="#um-edit-user-dialog"
                                    data-id="<?=$user['id']?>"
                                    data-username="<?=$user['username']?>"
                                    data-email="<?=$user['email']?>"
                                    data-level="<?=$user['level']?>"
                                    data-firstName="<?=$user['firstName']?>"
                                    data-tussenvoegsel="<?=$user['tussenvoegsel']?>"
                                    data-lastName="<?=$user['lastName']?>"
                                    data-role="<?=$user['role']?>"
                                    data-comments="<?=$user['comments']?>"
                                    data-avatar="<?=$user['avatar']?>"
                                    data-hideFromMemberList="<?=$user['hideFromMemberList']?>">
                                <span class="glyphicon glyphicon-pencil"></span>
                            </button>
                            <button class="um-resetpassword btn btn-sm btn-outline-cyndaron" data-id="<?=$user['id']?>">
                                <span class="glyphicon glyphicon-repeat"></span>
                            </button>
                            <button class="um-delete btn btn-sm btn-danger" data-id="<?=$user['id']?>">
                                <span class="glyphicon glyphicon-trash"></span>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

         <?php
        echo new Modal('um-edit-user-dialog', 'Gebruiker toevoegen/bewerken',
            '<input type="hidden" id="um-id" />
            <input type="hidden" id="um-csrf-token" />

            <div class="form-group row">
                <label for="um-username" class="col-sm-2 col-form-label">Gebruikersnaam:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="um-username">
                </div>
            </div>

            <div class="form-group row">
                <label for="um-email" class="col-sm-2 col-form-label">E-mailadres:</label>
                <div class="col-sm-10">
                    <input type="email" class="form-control" id="um-email">
                </div>
            </div>

            <div class="form-group row" id="um-password-group">
                <label for="um-password" class="col-sm-2 col-form-label">Wachtwoord:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="um-password" placeholder="Leeglaten voor een willekeurig wachtwoord">
                </div>
            </div>

            <div class="form-group row">
                <label for="um-level" class="col-sm-2 col-form-label">Gebruikersniveau:</label>
                <div class="col-sm-10">
                    <select id="um-level" class="custom-select">
                        <option value="1">' . self::USER_LEVEL_DESCRIPTIONS[1] . '</option>
                        <option value="4">' . self::USER_LEVEL_DESCRIPTIONS[4] . '</option>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label for="um-firstName" class="col-sm-2 col-form-label">Voornaam:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="um-firstName">
                </div>
            </div>

            <div class="form-group row">
                <label for="um-tussenvoegsel" class="col-sm-2 col-form-label">Tussenvoegsel:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="um-tussenvoegsel">
                </div>
            </div>

            <div class="form-group row">
                <label for="um-lastName" class="col-sm-2 col-form-label">Achternaam:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="um-lastName">
                </div>
            </div>

            <div class="form-group row">
                <label for="um-role" class="col-sm-2 col-form-label">Functie:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="um-role">
                </div>
            </div>

            <div class="form-group row">
                <label for="um-comments" class="col-sm-2 col-form-label">Opmerkingen:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="um-comments">
                </div>
            </div>

            <div class="form-group row">
                <label for="um-avatar" class="col-sm-2 col-form-label">Foto/avatar:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="um-avatar">
                </div>
            </div>
            
            <div class="form-group row">
                <div class="col-sm-12">
                    <input type="checkbox" class="" id="um-hideFromMemberList" value="1">
                    <label class="form-check-label" for="um-hideFromMemberList">Verbergen op Wie-is-wie</label>
                </div>
            </div>',
            '<button id="um-edit-user-save" type="button" class="btn btn-primary">Opslaan</button>
             <button type="button" class="btn btn-outline-cyndaron" data-dismiss="modal">Annuleren</button>',
            'modal-lg'
        );

        parent::showPostPage();
    }
}