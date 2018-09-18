<?php
namespace Cyndaron;

require_once __DIR__ . '/../check.php';

abstract class EditorPagina extends Pagina
{
    protected $id = null;
    protected $heeftTitel = true;
    protected $vorigeversie = null;
    protected $vvstring = '';
    protected $content;
    protected $titel;
    protected $type;
    protected $saveUrl;

    public function __construct()
    {
        $this->id = Request::geefGetVeilig('id');
        $this->vorigeversie = Request::geefGetVeilig('vorigeversie');
        $this->vvstring = $this->vorigeversie ? 'vorige' : '';
        $this->connectie = DBConnection::getPDO();

        $this->prepare();

        $_SESSION['referrer'] = htmlentities($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8');

        // Zorgen voor juiste codering
        $this->content = !empty($this->content) ? htmlentities($this->content, ENT_QUOTES, 'UTF-8') : '';

        if (empty($this->titel))
            $this->titel = '';

        $dir = dirname($_SERVER['PHP_SELF']);
        if ($dir == '/')
            $dir = '';

        parent::__construct('Editor');
        $this->maakNietDelen(true);
        $this->voegScriptToe('ckeditor/ckeditor.js');
        $this->voegScriptToe('sys/js/editor.js');
        $this->toonPrePagina();

        $unfriendlyUrl = new Url('toon' . $this->type . '.php?id=' . $this->id);
        $friendlyUrl = new Url($unfriendlyUrl->geefFriendly());

        if ($unfriendlyUrl == $friendlyUrl)
        {
            $friendlyUrl = "";
        }

        $saveUrl = sprintf($this->saveUrl, $this->id ? (string)$this->id : '');
        $protocol = Util::siteGebruiktTLS() ? 'https://' : 'http://';
        ?>

        <form name="bewerkartikel" method="post" action="<?=$saveUrl;?>" class="form-horizontal">

            <?php
            if ($this->heeftTitel === true):
                ?>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="titel">Titel: </label>
                    <div class="col-sm-5">
                        <input type="text" class="form-control" id="titel" name="titel" value="<?=$this->titel;?>" />
                    </div>
                </div>
                <?php
            endif;
            ?>
            <div class="form-group">
                <label class="col-sm-2 control-label" for="friendlyUrl">Friendly URL: </label>
                <div class="col-sm-5">
                    <div class="input-group">
                        <span class="input-group-addon"><?=$protocol . $_SERVER['HTTP_HOST'] . $dir;?>/</span>
                        <input type="text" class="form-control" id="friendlyUrl" name="friendlyUrl" value="<?=$friendlyUrl;?>" />
                    </div>

                </div>
            </div>

            <textarea class="ckeditor" name="artikel" rows="25" cols="125"><?=$this->content; ?></textarea>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="verwijzing">Interne link maken: </label>
                <div class="col-sm-5">
                    <select id="verwijzing" class="form-control form-control-inline">
                        <?php
                        $connectie = DBConnection::getPDO();
                        $sql = "
    SELECT * FROM (SELECT CONCAT('toonsub.php?id=', id) AS link, CONCAT('Statische pag.: ', naam) AS naam FROM subs ORDER BY naam ASC) AS twee
    UNION
    SELECT * FROM (SELECT CONCAT('tooncategorie.php?id=', id) AS link, CONCAT('Categorie: ', naam) AS naam FROM categorieen ORDER BY naam ASC) AS drie
    UNION
    SELECT * FROM (SELECT CONCAT('toonfotoboek.php?id=', id) AS link, CONCAT('Fotoboek: ', naam) AS naam FROM fotoboeken ORDER BY naam ASC) AS vijf;";

                        $links = $connectie->prepare($sql);
                        $links->execute();

                        foreach ($links->fetchAll() as $link)
                        {
                            echo '<option value="' . $link['link'] . '">' . $link['naam'] . '</option>';
                        }
                        ?>
                    </select>
                    <input type="button" id="plaklink" class="btn btn-default" value="Invoegen"/>
                </div>
            </div>

            <?php
            $this->toonSpecifiekeKnoppen();
            ?>
            <input type="submit" value="Opslaan" class="btn btn-primary"/>
            <a role="button" class="btn btn-default" href="<?=$_SESSION['referrer'];?>">Annuleren</a>

        </form>
        <?php
        $this->toonPostPagina();

    }

    abstract protected function prepare();

    abstract protected function toonSpecifiekeKnoppen();

    public function showCategoryDropdown()
    {
        ?>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="categorieid">Plaats dit artikel in de categorie: </label>
            <div class="col-sm-5">
                <select name="categorieid" class="form-control">
                    <option value="0">&lt;Geen categorie&gt;</option>
                    <?php

                    if ($this->id)
                    {
                        $categorieid = DBConnection::geefEen('SELECT categorieid FROM ' . $this->type . ' WHERE id= ?', [$this->id]);
                    }
                    else
                    {
                        $categorieid = Instelling::geefInstelling('standaardcategorie');
                    }

                    $categorieen = $this->connectie->query("SELECT * FROM categorieen ORDER BY naam;");
                    foreach ($categorieen->fetchAll() as $categorie)
                    {
                        if ($this->type == 'categorie' && $categorie['id'] == $this->id)
                            continue;

                        $selected = ($categorieid == $categorie['id']) ? ' selected="selected"' : '';
                        printf('<option value="%d" %s>%s</option>', $categorie['id'], $selected, $categorie['naam']);
                    }
                    ?>
                </select>
            </div>
        </div>
        <?php
    }
}