<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBConnection;
use Cyndaron\Page;
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

        $replies = DBConnection::doQueryAndFetchAll(
            "SELECT *,DATE_FORMAT(created, '%d-%m-%Y') AS friendlyDate,DATE_FORMAT(created, '%H:%i') AS friendlyTime FROM sub_replies WHERE subId=? ORDER BY created ASC",
            [$id]);

        parent::__construct($this->model->name);

        $this->renderAndEcho([
            'model' => $this->model,
            'text' => Util::parseText($this->model->text),
            'replies' => $replies,
        ]);
    }
}
