<?php


namespace Cyndaron\User;

use Cyndaron\DBConnection;
use Cyndaron\Pagina;

require_once __DIR__ . '/../../check.php';

class UserManagerPage extends Pagina
{
    public function __construct()
    {
        parent::__construct('Gebruikersbeheer');
        $this->voegScriptToe('/src/User/UserManagerPage.js');
        parent::toonPrepagina();

        $users = DBConnection::getInstance()->doQueryAndFetchAll('SELECT * FROM gebruikers ORDER BY gebruikersnaam', []);
        ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Gebruikersnaam</th>
                    <th>E-mailadres</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?=$user['id']?></td>
                    <td><?=$user['gebruikersnaam']?></td>
                    <td><?=$user['email']?></td>
                    <td>
                        <div class="btn-group">
                            <button class="um-resetpassword btn btn-sm btn-outline-cyndaron" data-id="<?=$user['id']?>">
                                <span class="glyphicon glyphicon-repeat"></span>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        parent::toonPostPagina();
    }
}