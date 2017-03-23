<?php
namespace Cyndaron\Ideeenbus;

use Cyndaron\DBConnection;
use Cyndaron\Gebruiker;
use Cyndaron\Pagina;
use Cyndaron\Request;
use Cyndaron\Widget\GoedeMelding;
use Cyndaron\Widget\Knop;


class IdeeenbusPagina extends Pagina
{
    public function __construct()
    {
        parent::__construct('Idee&euml;nbus');
        $this->toonPrePagina();
        $actie = Request::geefGetVeilig('actie');
        $connectie = DBConnection::getPDO();

        if ($actie == 'verwijderen' && Gebruiker::isAdmin())
        {
            $id = intval(Request::geefGetVeilig('id'));
            $deletion = $connectie->prepare('DELETE FROM ideeen WHERE id=?');
            $deletion->execute([$id]);
            echo new GoedeMelding('Idee verwijderd.');
        }

        if (!Request::postIsLeeg())
        {
            $naam = Request::geefPostVeilig('naam');
            $tekst = Request::geefPostVeilig('idee');
            $input = $connectie->prepare('INSERT INTO ideeen(id, naam, tekst, datum) VALUES (NULL, ?, ?, CURRENT_TIMESTAMP)');
            $input->execute([$naam, $tekst]);
            echo new GoedeMelding('Uw idee is succesvol ingediend.');
        }
        else
        {
            ?>
            <div class="col-lg-6" style="float: initial; margin-bottom: 25px;">
                <form method="post" action="ideeenbus.php">
                    <div class="form-group">
                        <label for="naam">Uw naam: </label>
                        <input type="text" id="naam" name="naam" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="idee">Uw idee: </label>
                        <textarea id="idee" name="idee" rows="6" cols="60" class="form-control"></textarea><br/>
                    </div>
                    <input type="submit" value="Versturen" class="btn btn-primary">
                </form>
            </div>

            <?php
        }
        $inhoud = $connectie->prepare("SELECT *, DATE_FORMAT(datum, '%d-%m-%Y') AS datumfriendly FROM ideeen ORDER BY id DESC ;");
        $inhoud->execute();
        foreach ($inhoud->fetchAll() as $idee)
        {
            $knopcode = Gebruiker::isAdmin() ? new Knop('verwijderen', 'ideeenbus.php?actie=verwijderen&amp;id=' . $idee['id'], 'Verwijder dit idee') : '';

            echo '<div class="panel panel-default"><div class="panel-heading"><h3 class="panel-title">';
            printf('Idee van <strong>%s</strong>: %s', $idee['naam'], $knopcode);
            echo '</h3></div><div class="panel-body">';
            echo $idee['tekst'];

            if ($idee['datumfriendly'] != '00-00-0000')
            {
                echo '<br /><br />';
                echo '<i>Achtergelaten op ' . $idee['datumfriendly'] . '</i>.';
            }

            echo '</div></div>';
        }

        $this->toonPostPagina();
    }
}