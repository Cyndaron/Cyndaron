<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBConnection;
use Cyndaron\Pagina;
use Cyndaron\Request;

class StaticPage extends Pagina
{
    public function __construct(int $id)
    {
        $connection = DBConnection::getPDO();
        if ($id <= 0)
        {
            header('Location: /error/404');
            die('Incorrecte parameter ontvangen.');
        }

        $model = new StaticPageModel($id);
        if (!$model->laden())
        {
            header('Location: /error/404');
            die('Pagina bestaat niet.');
        }

        $reactiesAan = $model->getEnableComments();

        if ($reactiesAan && !Request::postIsLeeg())
        {
            $auteur = Request::geefPostVeilig('auteur');
            $reactie = Request::geefPostVeilig('reactie');
            $antispam = strtolower(Request::geefPostVeilig('antispam'));
            if ($auteur && $reactie && ($antispam == 'acht' || $antispam == '8'))
            {
                $datum = date('Y-m-d H:i:s');
                $prep = $connection->prepare('INSERT INTO reacties(subid, auteur, tekst, datum) VALUES (?, ?, ?, ?)');
                $prep->execute([$id, $auteur, $reactie, $datum]);
            }
        }

        $controls = sprintf('<a href="/editor/sub/%d" class="btn btn-outline-cyndaron" title="Bewerk deze statische pagina"><span class="glyphicon glyphicon-pencil"></span></a>', $id);
        $controls .= sprintf('<a href="overzicht?type=sub&amp;actie=verwijderen&amp;id=%d" class="btn btn-outline-cyndaron" title="Verwijder deze statische pagina"><span class="glyphicon glyphicon-trash"></span></a>', $id);

        if (DBConnection::doQueryAndFetchOne('SELECT * FROM vorigesubs WHERE id= ?', [$id]))
        {
            $controls .= sprintf('<a href="/editor/sub/%d/previous" class="btn btn-outline-cyndaron" title="Vorige versie"><span class="glyphicon glyphicon-lastversion"></span></a>', $id);
        }
        parent::__construct($model->getName());
        $this->setTitleButtons($controls);
        $this->showPrePage();

        echo $model->getText();

        $prep = $connection->prepare("SELECT *,DATE_FORMAT(datum, '%d-%m-%Y') AS rdatum,DATE_FORMAT(datum, '%H:%i') AS rtijd FROM reacties WHERE subid=? ORDER BY datum ASC");
        $prep->execute([$id]);
        $reacties = $prep->fetchAll();
        $reactiesaanwezig = false;

        if (count($reacties) > 0)
        {
            $reactiesaanwezig = true;
            echo '<hr>';

            foreach ($reacties as $reactie)
            {
                echo '<div class="panel panel-default"><div class="panel-heading"><h3 class="panel-title">';
                printf('Reactie van <strong>%s</strong> op %s om %s:', $reactie['auteur'], $reactie['rdatum'], $reactie['rtijd']);
                echo '</h3></div><div class="panel-body">';
                echo $reactie['tekst'];
                echo '</div></div>';
            }
        }

        if ($reactiesaanwezig || $reactiesAan)
        {
            echo '<div class="reactiecontainer"><br />';
        }

        if ($reactiesaanwezig && !$reactiesAan)
        {
            echo 'Op dit bericht kan niet (meer) worden gereageerd.<br />';
        }
        if ($reactiesAan):
            ?>
            <h3>Reageren:</h3>
            <form name="reactie" method="post" action="/sub/<?= $id; ?>" class="form-horizontal">
                <div class="form-group">
                    <label for="auteur" class="col-sm-1 control-label">Naam: </label>
                    <div class="col-sm-4">
                        <input id="auteur" name="auteur" maxlength="100" class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="reactie" class="col-sm-1 control-label">Reactie: </label>
                    <div class="col-sm-4">
                        <textarea style="height: 100px;" id="reactie" name="reactie" class="form-control"></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label for="antispam" class="col-sm-1 control-label">Hoeveel is de wortel uit 64?: </label>
                    <div class="col-sm-4">
                        <input id="antispam" name="antispam" class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-1 col-sm-4">
                        <input type="submit" class="btn btn-primary" value="Versturen"/>
                    </div>
                </div>
            </form>
            <?php
        endif;

        if ($reactiesaanwezig || $reactiesAan)
        {
            echo '</div>';
        }

        $this->showPostPage();
    }
}
