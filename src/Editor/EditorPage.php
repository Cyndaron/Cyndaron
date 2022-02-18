<?php
namespace Cyndaron\Editor;

use Cyndaron\Category\Category;
use Cyndaron\DBAL\DBConnection;
use Cyndaron\Category\ModelWithCategory;
use Cyndaron\View\Page;
use Cyndaron\Util\Setting;
use Cyndaron\Url;
use Cyndaron\Util\Util;

use function Safe\scandir;
use function sprintf;
use function substr;
use function trim;
use function is_dir;
use function array_filter;

abstract class EditorPage extends Page
{
    public const TYPE = null;
    public const TABLE = null;
    public const HAS_TITLE = true;
    public const HAS_CATEGORY = false;
    public const SAVE_URL = '';

    public const CKEDITOR_IMAGES = [
        '/vendor/ckeditor/ckeditor/skins/moono-lisa/icons.png',
        '/vendor/ckeditor/ckeditor/skins/moono-lisa/icons_hidpi.png',
    ];

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
        $this->addScript('/js/editor.js');

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
        $this->templateVars['editorHeaderImage'] = ($this->model instanceof ModelWithCategory) ? $this->model->image : '';
        $this->templateVars['editorPreviewImage'] = ($this->model instanceof ModelWithCategory) ? $this->model->previewImage : '';
        $this->templateVars['blurb'] = ($this->model instanceof ModelWithCategory) ? $this->model->blurb : '';

        $this->templateVars['internalLinks'] = $internalLinks;

        if (static::HAS_CATEGORY)
        {
            $this->templateVars['categories'] = Category::fetchAll([], [], 'ORDER BY name');
            $this->templateVars['selectedCategories'] = [];
            if ($this->id && $this->model instanceof ModelWithCategory)
            {
                $categories = $this->model->getCategories();
                foreach ($categories as $category)
                {
                    $this->templateVars['selectedCategories'][$category->id] = 1;
                }
            }
            else
            {
                $this->templateVars['selectedCategories'][Setting::get('defaultCategory')] = 1;
            }

            $showBreadcrumbs = false;
            if ($this->id)
            {
                /** @noinspection SqlResolve */
                $showBreadcrumbs = (bool)DBConnection::doQueryAndFetchOne('SELECT showBreadcrumbs FROM ' . static::TABLE . ' WHERE id=?', [$this->id]);
            }

            $this->templateVars['showBreadcrumbs'] = $showBreadcrumbs;

            $pageHeaderImages = [];
            $pageHeaderDir = Util::UPLOAD_DIR . '/images/page-header';
            if (is_dir($pageHeaderDir))
            {
                $pageHeaderImages = array_filter(scandir($pageHeaderDir), static function($filename)
                {
                    return substr($filename, 0, 1) !== '.';
                });
            }
            $pagePreviewImages = [];
            $pagePreviewDir = Util::UPLOAD_DIR . '/images/page-preview';
            if (is_dir($pagePreviewDir))
            {
                $pagePreviewImages = array_filter(scandir($pagePreviewDir), static function($filename)
                {
                    return substr($filename, 0, 1) !== '.';
                });
            }

            $this->templateVars['pageHeaderImages'] = $pageHeaderImages;
            $this->templateVars['pagePreviewImages'] = $pagePreviewImages;
        }
    }

    abstract protected function prepare(): void;
}
