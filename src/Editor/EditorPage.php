<?php
namespace Cyndaron\Editor;

use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\Setting;
use Cyndaron\Url;
use Cyndaron\User\User;

require_once __DIR__ . '/../../check.php';

abstract class EditorPage extends Page
{
    protected $id = null;
    protected $hasTitle = true;
    protected $vorigeversie = false;
    protected $vvstring = '';
    protected $content;
    protected $contentTitle;
    protected $type;
    protected $table;
    protected $saveUrl;
    protected $record = [];

    public function __construct()
    {
        $this->id = Request::getVar(2);
        $this->vorigeversie = Request::getVar(3) === 'previous';
        $this->vvstring = $this->vorigeversie ? 'vorige' : '';

        $this->prepare();

        $_SESSION['referrer'] = htmlentities($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8');

        // Zorgen voor juiste codering
        $this->content = !empty($this->content) ? htmlentities($this->content, ENT_QUOTES, 'UTF-8') : '';

        if (empty($this->contentTitle))
            $this->contentTitle = '';

        $dir = dirname($_SERVER['PHP_SELF']);
        if ($dir == '/')
            $dir = '';

        parent::__construct('Editor');
        $this->addScript('/ckeditor/ckeditor.js');
        $this->addScript('/sys/js/editor.js');
        $this->showPrePage();

        $unfriendlyUrl = new Url('/' . $this->type . '/' . $this->id);
        $friendlyUrl = new Url($unfriendlyUrl->getFriendly());

        if ($unfriendlyUrl == $friendlyUrl)
        {
            $friendlyUrl = "";
        }

        $saveUrl = sprintf($this->saveUrl, $this->id ? (string)$this->id : '');
        $protocol = 'https://';
        ?>

        <form name="bewerkartikel" method="post" action="<?=$saveUrl;?>" class="form-horizontal">

            <?php
            if ($this->hasTitle === true):
                ?>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label" for="titel">Titel: </label>
                    <div class="col-sm-5">
                        <input type="text" class="form-control" id="titel" name="titel" value="<?=$this->contentTitle;?>" />
                    </div>
                </div>
                <?php
            endif;
            ?>

            <div class="form-group row">
                <label class="col-sm-2 col-form-label" for="friendlyUrl">Friendly URL: </label>
                <div class="col-sm-5">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon3">https://<?=$_SERVER['HTTP_HOST']?>/</span>
                        </div>
                        <input type="text" class="form-control" id="friendlyUrl" name="friendlyUrl" aria-describedby="basic-addon3" value="<?=trim($friendlyUrl,'/')?>"/>
                    </div>
                </div>
            </div>

            <textarea class="ckeditor" name="artikel" rows="25" cols="125"><?=$this->content; ?></textarea>

            <div class="form-group row">
                <label class="col-sm-2 col-form-label" for="verwijzing">Interne link maken: </label>
                <div class="col-sm-5">
                    <select id="verwijzing" class="form-control form-control-inline custom-select">
                        <?php
                        $connection = DBConnection::getPDO();
                        $sql = "
    SELECT * FROM (SELECT CONCAT('/sub/', id) AS link, CONCAT('Statische pag.: ', naam) AS naam FROM subs ORDER BY naam ASC) AS twee
    UNION
    SELECT * FROM (SELECT CONCAT('/category/', id) AS link, CONCAT('Categorie: ', naam) AS naam FROM categorieen ORDER BY naam ASC) AS drie
    UNION
    SELECT * FROM (SELECT CONCAT('/photoalbum/', id) AS link, CONCAT('Fotoalbum: ', naam) AS naam FROM fotoboeken ORDER BY naam ASC) AS vijf;";

                        $links = $connection->prepare($sql);
                        $links->execute();

                        foreach ($links->fetchAll() as $link)
                        {
                            echo '<option value="' . $link['link'] . '">' . $link['naam'] . '</option>';
                        }
                        ?>
                    </select>
                    <input type="button" id="plaklink" class="btn btn-outline-cyndaron" value="Invoegen"/>
                </div>
            </div>

            <?php
            $this->showContentSpecificButtons();
            ?>
            <input type="hidden" name="csrfToken" value="<?=User::getCSRFToken('editor', $this->type);?>"/>
            <input type="submit" value="Opslaan" class="btn btn-primary"/>
            <a role="button" class="btn btn-outline-cyndaron" href="<?=$_SESSION['referrer'];?>">Annuleren</a>

        </form>
        <?php
        $this->showPostPage();

    }

    abstract protected function prepare();

    abstract protected function showContentSpecificButtons();

    public function showCategoryDropdown()
    {
        ?>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="categorieid">Plaats dit artikel in de categorie: </label>
            <div class="col-sm-5">
                <select name="categorieid" class="form-control custom-select">
                    <option value="0">&lt;Geen categorie&gt;</option>
                    <?php

                    if ($this->id)
                    {
                        $categorieid = DBConnection::doQueryAndFetchOne('SELECT categorieid FROM ' . $this->table . ' WHERE id= ?', [$this->id]);
                    }
                    else
                    {
                        $categorieid = Setting::get('standaardcategorie');
                    }

                    $categorieen = DBConnection::doQueryAndFetchAll("SELECT * FROM categorieen ORDER BY naam;");
                    foreach ($categorieen as $categorie)
                    {
                        if ($this->type == 'category' && $categorie['id'] == $this->id)
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

    protected function showCheckbox(string $id, string $description, bool $checked)
    {
        ?>
        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="<?=$id?>" name="<?=$id?>" <?=$checked ? 'checked' : ''?> value="1">
            <label class="form-check-label" for="<?=$id?>"><?=$description?></label>
        </div>
        <?php
    }
}