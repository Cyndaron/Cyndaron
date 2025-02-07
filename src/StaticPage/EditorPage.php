<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBAL\Connection;

final class EditorPage extends \Cyndaron\Editor\EditorPage
{
    public const TYPE = 'sub';
    public const SAVE_URL = '/editor/sub/%s';
    public const HAS_CATEGORY = true;

    public string $template = '';

    public function __construct(
        private readonly StaticPageRepository $staticPageRepository,
        private readonly Connection $connection
    ) {

    }

    public function prepare(): void
    {
        $enableComments = false;
        $tags = '';
        if ($this->id)
        {
            $staticPage = $this->staticPageRepository->fetchById($this->id);
            $this->model = $staticPage;
            $table = ($this->useBackup) ? 'sub_backups' : 'subs';
            /**
             * @noinspection SqlResolve
             * @var string $content
             */
            $content = $this->connection->doQueryAndFetchOne('SELECT text FROM ' . $table . ' WHERE id=?', [$this->id]);
            $this->content = $content;
            /**
             * @noinspection SqlResolve
             * @var string $contentTitle
             */
            $contentTitle = $this->connection->doQueryAndFetchOne('SELECT name FROM ' . $table . ' WHERE id=?', [$this->id]);
            $this->contentTitle = $contentTitle;

            if ($staticPage !== null)
            {
                $enableComments = $staticPage->enableComments;
                $tags = $staticPage->tags;
                $this->linkedCategories = $this->staticPageRepository->getLinkedCategories($staticPage);
            }
        }

        $this->templateVars['enableComments'] = $enableComments;
        $this->templateVars['tags'] = $tags;
    }
}
