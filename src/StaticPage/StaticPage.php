<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Page\Page;
use Cyndaron\View\Renderer\TextRenderer;

final class StaticPage extends Page
{
    public function __construct(StaticPageModel $model, TextRenderer $textRenderer)
    {
        $this->model = $model;

        $replies = DBConnection::getPDO()->doQueryAndFetchAll(
            "SELECT *,DATE_FORMAT(created, '%d-%m-%Y') AS friendlyDate,DATE_FORMAT(created, '%H:%i') AS friendlyTime FROM sub_replies WHERE subId=? ORDER BY created ASC",
            [$model->id]
        );

        parent::__construct($this->model->name);

        $this->addTemplateVars([
            'model' => $model,
            'text' => $textRenderer->render($model->text),
            'replies' => $replies,
            'pageImage' => $model->getImage(),
        ]);
    }
}
