<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\User\User;

class StaticPage extends Page
{
    public function __construct(int $id)
    {
        $connection = DBConnection::getPDO();
        if ($id <= 0)
        {
            header('Location: /error/404');
            die('Incorrecte parameter ontvangen.');
        }

        $this->model = new StaticPageModel($id);
        $this->model->load();
        if ($this->model === null)
        {
            header('Location: /error/404');
            die('Pagina bestaat niet.');
        }

        $allowReplies = $this->model->enableComments;

        $controls = sprintf('<a href="/editor/sub/%d" class="btn btn-outline-cyndaron" title="Bewerk deze statische pagina"><span class="glyphicon glyphicon-pencil"></span></a>', $id);
        $controls .= sprintf('<a href="overzicht?type=sub&amp;actie=verwijderen&amp;id=%d" class="btn btn-outline-cyndaron" title="Verwijder deze statische pagina"><span class="glyphicon glyphicon-trash"></span></a>', $id);

        if ($this->model->hasBackup())
        {
            $controls .= sprintf('<a href="/editor/sub/%d/previous" class="btn btn-outline-cyndaron" title="Vorige versie"><span class="glyphicon glyphicon-lastversion"></span></a>', $id);
        }

        parent::__construct($this->model->name);
        $this->setTitleButtons($controls);
        $this->showPrePage();

        echo $this->model->text;

        $prep = $connection->prepare("SELECT *,DATE_FORMAT(created, '%d-%m-%Y') AS friendlyDate,DATE_FORMAT(created, '%H:%i') AS friendlyTime FROM sub_replies WHERE subId=? ORDER BY created ASC");
        $prep->execute([$id]);
        $replies = $prep->fetchAll();
        $hasReplies = count($replies) > 0;

        if ($hasReplies)
        {
            foreach ($replies as $reply): ?>
                <article class="card mb-2">
                    <div class="card-header">
                        Reactie van <strong><?=$reply['author']?></strong> op <time datetime="<?=$reply['created']?>"><?=$reply['friendlyDate']?> om <?=$reply['friendlyTime']?></time>
                    </div>
                    <div class="card-body">
                        <?=$reply['text']?>
                    </div>
                </article>
            <?php endforeach;
        }

        if ($hasReplies && !$allowReplies)
        {
            echo 'Op dit bericht kan niet (meer) worden gereageerd.<br />';
        }
        if ($allowReplies):
            ?>
            <h3>Reageren:</h3>
            <form name="reactie" method="post" action="/sub/react/<?= $id; ?>" class="form-horizontal">
                <div class="form-group row">
                    <label for="author" class="col-sm-3 col-form-label">Naam: </label>
                    <div class="col-sm-9">
                        <input id="author" name="author" maxlength="100" class="form-control"/>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="reactie" class="col-sm-3 col-form-label">Reactie: </label>
                    <div class="col-sm-9">
                        <textarea style="height: 100px;" id="reactie" name="reactie" class="form-control"></textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="antispam" class="col-sm-3 col-form-label">Hoeveel is de wortel uit 64?: </label>
                    <div class="col-sm-9">
                        <input id="antispam" name="antispam" class="form-control"/>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-offset-1 col-sm-9">
                        <input type="submit" class="btn btn-primary" value="Versturen"/>
                    </div>
                </div>
                <input type="hidden" name="csrfToken" value="<?=User::getCSRFToken('sub', 'react')?>"/>
            </form>
            <?php
        endif;

        $this->showPostPage();
    }
}
