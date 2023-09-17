<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Page\Page;
use Cyndaron\View\Template\ViewHelpers;

final class StaticPage extends Page
{
    public function __construct(StaticPageModel $model)
    {
        $this->model = $model;

        $replies = DBConnection::doQueryAndFetchAll(
            "SELECT *,DATE_FORMAT(created, '%d-%m-%Y') AS friendlyDate,DATE_FORMAT(created, '%H:%i') AS friendlyTime FROM sub_replies WHERE subId=? ORDER BY created ASC",
            [$model->id]
        );

        parent::__construct($this->model->name);

        $this->addTemplateVars([
            'model' => $model,
            'text' => ViewHelpers::parseText($model->text),
            'replies' => $replies,
            'pageImage' => $model->getImage(),
        ]);
    }
}
