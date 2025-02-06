<?php
declare(strict_types=1);

namespace Cyndaron\StaticPage;

use Cyndaron\DBAL\Connection;
use Cyndaron\Page\Page;
use Cyndaron\View\Renderer\TextRenderer;

final class StaticPage extends Page
{
    public function __construct(StaticPageModel $model, StaticPageRepository $staticPageRepository, Connection $connection, TextRenderer $textRenderer)
    {
        $this->model = $model;
        $this->category = $staticPageRepository->getFirstLinkedCategory($model);

        $replies = $connection->doQueryAndFetchAll(
            "SELECT *,DATE_FORMAT(created, '%d-%m-%Y') AS friendlyDate,DATE_FORMAT(created, '%H:%i') AS friendlyTime FROM sub_replies WHERE subId=? ORDER BY created ASC",
            [$model->id]
        );

        $this->title = $this->model->name;

        $this->addTemplateVars([
            'model' => $model,
            'text' => $textRenderer->render($model->text),
            'replies' => $replies,
            'pageImage' => $model->getImage(),
        ]);
    }
}
