<?php
namespace Cyndaron;


class StatischePagina extends Pagina
{
    public function __construct()
    {
        $connectie = DBConnection::getPDO();
        $subid = intval(Request::geefGetVeilig('id'));
        if (!is_numeric($subid) || $subid <= 0)
        {
            header('Location: 404.php');
            die('Incorrecte parameter ontvangen.');
        }

        $model = new StatischePaginaModel($subid);
        if (!$model->laden())
        {
            header('Location: 404.php');
            die('Pagina bestaat niet.');
        }

        $reactiesAan = $model->getReactiesAan();

        if ($reactiesAan && !Request::postIsLeeg())
        {
            $auteur = Request::geefPostVeilig('auteur');
            $reactie = Request::geefPostVeilig('reactie');
            $antispam = strtolower(Request::geefPostVeilig('antispam'));
            if ($auteur && $reactie && ($antispam == 'acht' || $antispam == '8'))
            {
                $datum = date('Y-m-d H:i:s');
                $prep = $connectie->prepare('INSERT INTO reacties(subid, auteur, tekst, datum) VALUES (?, ?, ?, ?)');
                $prep->execute([$subid, $auteur, $reactie, $datum]);
            }
        }

        $controls = sprintf('<a href="editor-statischepagina?id=%d" class="btn btn-outline-cyndaron" title="Bewerk deze statische pagina"><span class="glyphicon glyphicon-pencil"></span></a>', $subid);
        $controls .= sprintf('<a href="overzicht?type=sub&amp;actie=verwijderen&amp;id=%d" class="btn btn-outline-cyndaron" title="Verwijder deze statische pagina"><span class="glyphicon glyphicon-trash"></span></a>', $subid);

        if (DBConnection::geefEen('SELECT * FROM vorigesubs WHERE id= ?', [$subid]))
        {
            $controls .= sprintf('<a href="editor-statischepagina?vorigeversie=1&amp;id=%d" class="btn btn-outline-cyndaron" title="Vorige versie"><span class="glyphicon glyphicon-lastversion"></span></a>', $subid);
        }
        parent::__construct($model->getNaam());
        $this->maakTitelknoppen($controls);
        $this->toonPrePagina();

        echo $model->getTekst();

        $prep = $connectie->prepare("SELECT *,DATE_FORMAT(datum, '%d-%m-%Y') AS rdatum,DATE_FORMAT(datum, '%H:%i') AS rtijd FROM reacties WHERE subid=? ORDER BY datum ASC");
        $prep->execute([$subid]);
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
            <form name="reactie" method="post" action="toonsub.php?id=<?= $subid; ?>" class="form-horizontal">
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

        $this->toonPostPagina();
    }
}
