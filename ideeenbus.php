<?php
require_once('functies.db.php');
require_once('functies.gebruikers.php');
require_once('pagina.php');

class Ideeenbus extends Pagina
{
    public function __construct()
    {
        parent::__construct('Idee&euml;nbus');
        $this->toonPrePagina();
        $actie = geefGetVeilig('actie');
        $connectie = newPDO();

        if ($actie == 'verwijderen' && isAdmin())
        {
            $id = intval(geefGetVeilig('id'));
            $deletion = $connectie->prepare('DELETE FROM ideeen WHERE id=?');
            $deletion->execute(array($id));
            echo new GoedeMelding('Idee verwijderd.');
        }

        if (!postIsLeeg())
        {
            $naam = geefPostVeilig('naam');
            $tekst = geefPostVeilig('idee');
            $input = $connectie->prepare('INSERT INTO ideeen VALUES (NULL, ?, ?)');
            $input->execute(array($naam, $tekst));
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
        $inhoud = $connectie->prepare("SELECT id, naam, tekst FROM ideeen ORDER BY id DESC ;");
        $inhoud->execute();
        foreach ($inhoud->fetchAll() as $idee)
        {
            $knopcode = isAdmin() ? knopcode('verwijderen', 'ideeenbus.php?actie=verwijderen&amp;id=' . $idee['id'], 'Verwijder dit idee') : '';

            echo '<div class="panel panel-default"><div class="panel-heading"><h3 class="panel-title">';
            printf('Idee van <strong>%s</strong>: %s', $idee['naam'], $knopcode);
            echo '</h3></div><div class="panel-body">';
            echo $idee['tekst'];
            echo '</div></div>';
        }

        $this->toonPostPagina();
    }
}

$pagina = new Ideeenbus();