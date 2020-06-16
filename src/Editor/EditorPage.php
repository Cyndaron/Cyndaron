<?php
namespace Cyndaron\Editor;

use Cyndaron\Category\Category;
use Cyndaron\DBConnection;
use Cyndaron\ModelWithCategory;
use Cyndaron\Page;
use Cyndaron\Setting;
use Cyndaron\Url;

abstract class EditorPage extends Page
{
    public const TYPE = null;
    public const TABLE = null;
    public const HAS_TITLE = true;
    public const HAS_CATEGORY = false;
    public const SAVE_URL = '';

    protected ?int $id = null;

    protected bool $vorigeversie = false;
    protected string $vvstring = '';
    protected string $content = '';
    protected string $contentTitle = '';
    protected string $template = 'Editor/PageBase';

    public function __construct(array $internalLinks, ?int $id, bool $previous)
    {
        $this->id = $id;
        $this->vorigeversie = $previous;
        $this->vvstring = $this->vorigeversie ? 'vorige' : '';

        $this->prepare();

        parent::__construct('Editor');
        $this->addScript('/vendor/ckeditor/ckeditor/ckeditor.js');
        $this->addScript('/sys/js/editor.js');

        $unfriendlyUrl = new Url('/' . static::TYPE . '/' . $this->id);
        $friendlyUrl = $unfriendlyUrl->getFriendly();

        if ((string)$unfriendlyUrl === $friendlyUrl)
        {
            $friendlyUrl = '';
        }

        $saveUrl = sprintf(static::SAVE_URL, $this->id ? (string)$this->id : '');
        $this->templateVars['id'] = $this->id;
        $this->templateVars['saveUrl'] = $saveUrl;
        $this->templateVars['articleType'] = static::TYPE;
        $this->templateVars['hasTitle'] = static::HAS_TITLE;
        $this->templateVars['hasCategory'] = static::HAS_CATEGORY;
        $this->templateVars['contentTitle'] = $this->contentTitle;
        $this->templateVars['friendlyUrl'] = trim($friendlyUrl, '/');
        $this->templateVars['friendlyUrlPrefix'] = "https://{$_SERVER['HTTP_HOST']}/";
        $this->templateVars['article'] = $this->content;
        $this->templateVars['model'] = $this->model;
        $this->templateVars['image'] = ($this->model instanceof ModelWithCategory) ? $this->model->image : '';
        $this->templateVars['previewImage'] = ($this->model instanceof ModelWithCategory) ? $this->model->previewImage : '';
        $this->templateVars['blurb'] = ($this->model instanceof ModelWithCategory) ? $this->model->blurb : '';

        $this->templateVars['internalLinks'] = $internalLinks;

        if (static::HAS_CATEGORY)
        {
            if ($this->id)
            {
                /** @noinspection SqlResolve */
                $this->templateVars['categoryId'] = (int)DBConnection::doQueryAndFetchOne('SELECT categoryId FROM ' . static::TABLE . ' WHERE id= ?', [$this->id]);
            }
            else
            {
                $this->templateVars['categoryId'] = (int)Setting::get('defaultCategory');
            }

            $this->templateVars['categories'] = Category::fetchAll([], [], 'ORDER BY name');

            $showBreadcrumbs = false;
            if ($this->id)
            {
                /** @noinspection SqlResolve */
                $showBreadcrumbs = (bool)DBConnection::doQueryAndFetchOne('SELECT showBreadcrumbs FROM ' . static::TABLE . ' WHERE id=?', [$this->id]);
            }

            $this->templateVars['showBreadcrumbs'] = $showBreadcrumbs;
        }
    }

    abstract protected function prepare();
}
