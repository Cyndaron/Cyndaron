<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\Photoalbum\Photoalbum;
use Cyndaron\Photoalbum\PhotoalbumCaption;
use Cyndaron\Photoalbum\PhotoalbumPage;
use Cyndaron\User\User;
use Cyndaron\Util;

class StaticPage extends Page
{
    public function __construct(int $id)
    {
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

        $replies = DBConnection::doQueryAndFetchAll(
            "SELECT *,DATE_FORMAT(created, '%d-%m-%Y') AS friendlyDate,DATE_FORMAT(created, '%H:%i') AS friendlyTime FROM sub_replies WHERE subId=? ORDER BY created ASC",
            [$id]);

        parent::__construct($this->model->name);
        $this->setTitleButtons($controls);
        $this->showPrePage();

        $this->templateVars['text'] = Util::parseText($this->model->text);
        $this->templateVars['replies'] = $replies;
        $this->templateVars['allowReplies'] = $allowReplies;

        $this->showPostPage();
    }
}
