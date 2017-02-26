<?php
require_once('functies.db.php');
require_once('functies.gebruikers.php');
require_once('functies.pagina.php');
require_once('pagina.php');

class StatischePagina extends Pagina
{
    public function __construct()
    {
        $connectie = newPDO();
        $subid = intval($_GET['id']);
        if (!is_numeric($subid) || $subid <= 0)
        {
            header('Location: 404.php');
            die('Incorrecte parameter ontvangen.');
        }
        $subnaam = geefEen('SELECT naam FROM subs WHERE id=?', array($subid));
        $reactiesaan = geefEen('SELECT reacties_aan FROM subs WHERE id=?', array($subid));
        $referrer = htmlentities(@$_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8');
        if ($reactiesaan && !empty($_POST))
        {
            $auteur = $_POST['auteur'];
            $reactie = $_POST['reactie'];
            $antispam = strtolower($_POST['antispam']);
            if ($auteur && $reactie && ($antispam == 'acht' || $antispam == '8'))
            {
                $datum = date('Y-m-d H:i:s');
                $prep = $connectie->prepare('INSERT INTO reacties(subid, auteur, tekst, datum) VALUES (?, ?, ?, ?)');
                $prep->execute(array($subid, $auteur, $reactie, $datum));
            }
        }

        $controls = sprintf('<a href="editor.php?type=sub&amp;id=%d" class="btn btn-default" title="Bewerk deze sub"><span class="glyphicon glyphicon-pencil"></span></a>', $subid);
        $controls .= sprintf('<a href="overzicht.php?type=sub&amp;actie=verwijderen&amp;id=%d" class="btn btn-default" title="Verwijder deze sub"><span class="glyphicon glyphicon-trash"></span></a>', $subid);

        if (geefEen('SELECT * FROM vorigesubs WHERE id= ?', array($subid)))
        {
            $controls .= sprintf('<a href="editor.php?type=sub&amp;vorigeversie=1&amp;id=%d" class="btn btn-default" title="Vorige versie"><span class="glyphicon glyphicon-vorige-versie"></span></a>', $subid);
        }
        parent::__construct($subnaam);
        $this->maakTitelknoppen($controls);
        $this->toonPrePagina();

        echo geefEen('SELECT tekst FROM subs WHERE id=?', array($subid));

        $prep = $connectie->prepare("SELECT *,DATE_FORMAT(datum, '%d-%m-%Y') AS rdatum,DATE_FORMAT(datum, '%H:%i') AS rtijd FROM reacties WHERE subid=? ORDER BY datum ASC");
        $prep->execute(array($subid));
        $reacties = $prep->fetchAll();
        $reactiesaanwezig = FALSE;

        if (count($reacties) > 0)
        {
            $reactiesaanwezig = TRUE;
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

        if ($reactiesaanwezig || $reactiesaan)
        {
            echo '<div class="reactiecontainer"><br />';
        }

        if ($reactiesaanwezig && !$reactiesaan)
        {
            echo 'Op dit bericht kan niet (meer) worden gereageerd.<br />';
        }
        if ($reactiesaan):
        ?>
            <h3>Reageren:</h3>
            <form name="reactie" method="post" action="toonsub.php?id=<?=$subid;?>" class="form-horizontal">
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
                        <input type="submit" class="btn btn-primary" value="Versturen" />
                    </div>
                </div>
            </form>
        <?php
        endif;

        if ($reactiesaanwezig || $reactiesaan)
        {
            echo '</div>';
        }

        $this->toonPostPagina();
    }
}
$pagina = new StatischePagina();