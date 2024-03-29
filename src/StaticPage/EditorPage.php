<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBAL\DBConnection;

final class EditorPage extends \Cyndaron\Editor\EditorPage
{
    public const TYPE = 'sub';
    public const SAVE_URL = '/editor/sub/%s';
    public const HAS_CATEGORY = true;

    protected string $template = '';

    protected function prepare(): void
    {
        $enableComments = false;
        $tags = '';
        if ($this->id)
        {
            $staticPage = StaticPageModel::fetchById($this->id);
            $this->model = $staticPage;
            $table = ($this->useBackup) ? 'sub_backups' : 'subs';
            /**
             * @noinspection SqlResolve
             * @var string $content
             */
            $content = DBConnection::getPDO()->doQueryAndFetchOne('SELECT text FROM ' . $table . ' WHERE id=?', [$this->id]);
            $this->content = $content;
            /**
             * @noinspection SqlResolve
             * @var string $contentTitle
             */
            $contentTitle = DBConnection::getPDO()->doQueryAndFetchOne('SELECT name FROM ' . $table . ' WHERE id=?', [$this->id]);
            $this->contentTitle = $contentTitle;

            if ($staticPage !== null)
            {
                $enableComments = $staticPage->enableComments;
                $tags = $staticPage->tags;
            }
        }

        $this->templateVars['enableComments'] = $enableComments;
        $this->templateVars['tags'] = $tags;
    }
}
