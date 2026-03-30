<?php
declare(strict_types=1);

namespace Cyndaron\StaticPage;

use Cyndaron\DBAL\Connection;
use Cyndaron\Page\Page;
use Cyndaron\View\Renderer\TextRenderer;

final class StaticPage extends Page
{
    public function __construct(StaticPageModel $model, StaticPageRepository $staticPageRepository, ReplyRepository $replyRepository, TextRenderer $textRenderer)
    {
        $this->model = $model;
        $this->category = $staticPageRepository->getFirstLinkedCategory($model);

        $this->title = $this->model->name;

        $this->addTemplateVars([
            'model' => $model,
            'text' => $textRenderer->render($model->text),
            'replies' => $replyRepository->fetchByStaticPage($model),
            'pageImage' => $model->getImage(),
            'hasBackup' => $staticPageRepository->hasBackup($model),
        ]);
    }
}
