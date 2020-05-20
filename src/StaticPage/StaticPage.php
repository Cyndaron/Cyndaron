<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\Template\ViewHelpers;

class StaticPage extends Page
{
    public function __construct(StaticPageModel $model)
    {
        $this->model = $model;

        $replies = DBConnection::doQueryAndFetchAll(
            "SELECT *,DATE_FORMAT(created, '%d-%m-%Y') AS friendlyDate,DATE_FORMAT(created, '%H:%i') AS friendlyTime FROM sub_replies WHERE subId=? ORDER BY created ASC",
            [$model->id]);

        parent::__construct($this->model->name);

        $this->addTemplateVars([
            'model' => $this->model,
            'text' => ViewHelpers::parseText($this->model->text),
            'replies' => $replies,
        ]);
    }
}
