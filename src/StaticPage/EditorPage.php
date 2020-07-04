<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBConnection;

final class EditorPage extends \Cyndaron\Editor\EditorPage
{
    public const TYPE = 'sub';
    public const TABLE = 'subs';
    public const SAVE_URL = '/editor/sub/%s';
    public const HAS_CATEGORY = true;

    protected string $template = '';

    protected function prepare(): void
    {
        $enableComments = false;
        $tags = '';
        if ($this->id)
        {
            $staticPage = StaticPageModel::loadFromDatabase($this->id);
            $this->model = $staticPage;
            $table = ($this->vvstring !== '') ? 'sub_backups' : self::TABLE;
            /** @noinspection SqlResolve */
            $this->content = DBConnection::doQueryAndFetchOne('SELECT text FROM ' . $table . ' WHERE id=?', [$this->id]);
            /** @noinspection SqlResolve */
            $this->contentTitle = DBConnection::doQueryAndFetchOne('SELECT name FROM ' . $table . ' WHERE id=?', [$this->id]);

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
