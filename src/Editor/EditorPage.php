<?php
namespace Cyndaron\Editor;

use Cyndaron\DBConnection;
use Cyndaron\Model;
use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\Setting;
use Cyndaron\Url;
use Cyndaron\User\User;

require_once __DIR__ . '/../../check.php';

abstract class EditorPage extends Page
{
    const TYPE = null;
    const TABLE = null;
    const HAS_TITLE = true;
    const HAS_CATEGORY = false;
    const SAVE_URL = '';

    protected $id = null;

    protected $vorigeversie = false;
    protected $vvstring = '';
    protected $content;
    protected $contentTitle;
    /** @var Model */
    protected $model = null;

    public function __construct()
    {
        $this->id = (int)Request::getVar(2);
        $this->vorigeversie = Request::getVar(3) === 'previous';
        $this->vvstring = $this->vorigeversie ? 'vorige' : '';

        $this->prepare();

        $_SESSION['referrer'] = htmlentities($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8');

        // Zorgen voor juiste codering
        $this->content = !empty($this->content) ? htmlentities($this->content, ENT_QUOTES, 'UTF-8') : '';

        if (empty($this->contentTitle))
            $this->contentTitle = '';

        parent::__construct('Editor');
        $this->addScript('/contrib/ckeditor/ckeditor.js');
        $this->addScript('/sys/js/editor.js');
        $this->showPrePage();

        $unfriendlyUrl = new Url('/' . static::TYPE . '/' . $this->id);
        $friendlyUrl = new Url($unfriendlyUrl->getFriendly());

        if ($unfriendlyUrl->equals($friendlyUrl))
        {
            $friendlyUrl = "";
        }

        $saveUrl = sprintf(static::SAVE_URL, $this->id ? (string)$this->id : '');
        ?>
        <form name="bewerkartikel" method="post" action="<?=$saveUrl;?>" class="form-horizontal">

            <?php
            if (static::HAS_TITLE):
                ?>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label" for="titel">Titel: </label>
                    <div class="col-sm-5">
                        <input type="text" class="form-control" id="titel" name="titel" value="<?=$this->contentTitle;?>" />
                    </div>
                </div>
                <?php
                if (static::HAS_CATEGORY)
                {
                    $showBreadcrumbs = false;
                    if ($this->id)
                    {
                        $showBreadcrumbs = (bool)DBConnection::doQueryAndFetchOne('SELECT showBreadcrumbs FROM ' . static::TABLE . ' WHERE id=?', [$this->id]);
                    }

                    $this->showCheckbox('showBreadcrumbs', 'Titel tonen als breadcrumbs', $showBreadcrumbs);
                }
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
            <?php endif; ?>

            <textarea class="ckeditor" name="artikel" rows="25" cols="125"><?=$this->content; ?></textarea>

            <div class="form-group row">
                <label class="col-sm-2 col-form-label" for="verwijzing">Interne link maken: </label>
                <div class="col-sm-5">
                    <select id="verwijzing" class="form-control form-control-inline custom-select">
                        <?php
                        $connection = DBConnection::getPDO();
                        $sql = "
    SELECT * FROM (SELECT CONCAT('/sub/', id) AS link, CONCAT('Statische pag.: ', name) AS name FROM subs ORDER BY name ASC) AS twee
    UNION
    SELECT * FROM (SELECT CONCAT('/category/', id) AS link, CONCAT('Categorie: ', name) AS name FROM categories ORDER BY name ASC) AS drie
    UNION
    SELECT * FROM (SELECT CONCAT('/photoalbum/', id) AS link, CONCAT('Fotoalbum: ', name) AS name FROM photoalbums ORDER BY name ASC) AS vijf
    UNION
    SELECT * FROM (SELECT CONCAT('/concert/order/', id) AS link, CONCAT('Concert: ', name) AS name FROM  ticketsale_concerts ORDER BY name ASC) AS vijf;";

                        $links = $connection->prepare($sql);
                        $links->execute();

                        foreach ($links->fetchAll() as $link)
                        {
                            echo '<option value="' . $link['link'] . '">' . $link['name'] . '</option>';
                        }
                        ?>
                    </select>
                    <input type="button" id="plaklink" class="btn btn-outline-cyndaron" value="Invoegen"/>
                </div>
            </div>

            <?php
            if (static::HAS_CATEGORY)
            {
                $this->showCategoryDropdown();
            }
            $this->showContentSpecificButtons();
            ?>
            <input type="hidden" name="csrfToken" value="<?=User::getCSRFToken('editor', static::TYPE);?>"/>
            <input type="submit" value="Opslaan" class="btn btn-primary"/>
            <a role="button" class="btn btn-outline-cyndaron" href="<?=$_SESSION['referrer'];?>">Annuleren</a>

        </form>
        <?php
        $this->showPostPage();

    }

    abstract protected function prepare();

    abstract protected function showContentSpecificButtons();

    private function showCategoryDropdown()
    {
        ?>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="categoryId">Plaats dit artikel in de categorie: </label>
            <div class="col-sm-5">
                <select name="categoryId" class="form-control custom-select">
                    <option value="0">&lt;Geen categorie&gt;</option>
                    <?php

                    if ($this->id)
                    {
                        $categoryId = DBConnection::doQueryAndFetchOne('SELECT categoryId FROM ' . static::TABLE . ' WHERE id= ?', [$this->id]);
                    }
                    else
                    {
                        $categoryId = Setting::get('defaultCategory');
                    }

                    $categorieen = DBConnection::doQueryAndFetchAll("SELECT * FROM categories ORDER BY name;");
                    foreach ($categorieen as $category)
                    {
                        if (static::TYPE == 'category' && $category['id'] == $this->id)
                            continue;

                        $selected = ($categoryId == $category['id']) ? ' selected="selected"' : '';
                        printf('<option value="%d" %s>%s</option>', $category['id'], $selected, $category['name']);
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