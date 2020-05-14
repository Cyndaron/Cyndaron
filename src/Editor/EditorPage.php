<?php
namespace Cyndaron\Editor;

use Cyndaron\Category\Category;
use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\Setting;
use Cyndaron\Url;

abstract class EditorPage extends Page
{
    const TYPE = null;
    const TABLE = null;
    const HAS_TITLE = true;
    const HAS_CATEGORY = false;
    const SAVE_URL = '';

    protected ?int $id = null;

    protected bool $vorigeversie = false;
    protected string $vvstring = '';
    protected string $content = '';
    protected string $contentTitle = '';
    protected string $template = 'Editor/PageBase';

    public function __construct(array $internalLinks)
    {
        $this->id = (int)Request::getVar(2);
        $this->vorigeversie = Request::getVar(3) === 'previous';
        $this->vvstring = $this->vorigeversie ? 'vorige' : '';

        $this->prepare();

        parent::__construct('Editor');
        $this->addScript('/vendor/ckeditor/ckeditor/ckeditor.js');
        $this->addScript('/sys/js/editor.js');

        $unfriendlyUrl = new Url('/' . static::TYPE . '/' . $this->id);
        $friendlyUrl = $unfriendlyUrl->getFriendly();

        if ($unfriendlyUrl == $friendlyUrl)
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