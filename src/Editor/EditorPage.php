<?php
namespace Cyndaron\Editor;

use Cyndaron\Category\Category;
use Cyndaron\Category\ModelWithCategory;
use Cyndaron\Page\Page;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\UrlInfo;
use Cyndaron\Url\Url;
use Cyndaron\Url\UrlService;
use Cyndaron\Util\Link;
use Cyndaron\Util\Setting;
use Cyndaron\Util\Util;
use Safe\Exceptions\DirException;
use Symfony\Component\HttpFoundation\Request;
use function array_filter;
use function is_dir;
use function Safe\scandir;
use function sprintf;
use function substr;
use function trim;

abstract class EditorPage extends Page
{
    public const TYPE = null;
    public const HAS_TITLE = true;
    public const HAS_CATEGORY = false;
    public const SAVE_URL = '';

    // Listed here to ensure they get copied to public_html
    public const CKEDITOR_IMAGES = [
        '/vendor/ckeditor/ckeditor/skins/moono-lisa/icons.png',
        '/vendor/ckeditor/ckeditor/skins/moono-lisa/icons_hidpi.png',
    ];

    protected int|null $id = null;

    protected bool $useBackup = false;
    protected string $content = '';
    protected string $contentTitle = '';
    public string $template = 'Editor/PageBase';
    protected QueryBits $queryBits;

    /**
     * @param QueryBits $queryBits
     * @param Request $request
     * @param UrlService $urlService
     * @param EditorVariables $editorVariables
     * @throws DirException
     */
    final public function __construct(QueryBits $queryBits, Request $request, UrlService $urlService, EditorVariables $editorVariables)
    {
        $this->queryBits = $queryBits;
        $this->id = $editorVariables->id;
        $this->useBackup = $editorVariables->useBackup;

        $this->prepare();

        $this->title = 'Editor';
        $this->addScript('/vendor/ckeditor/ckeditor/ckeditor.js');
        $this->addScript('/js/editor.js');

        $unfriendlyUrl = new Url('/' . static::TYPE . '/' . $this->id);
        $friendlyUrl = (string)$urlService->toFriendly($unfriendlyUrl);

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
        $this->templateVars['friendlyUrlPrefix'] = $request->getSchemeAndHttpHost() . '/';
        $this->templateVars['referrer'] = $request->headers->get('referer');
        $this->templateVars['article'] = $this->content;
        $this->templateVars['model'] = $this->model;
        $this->templateVars['editorHeaderImage'] = ($this->model instanceof ModelWithCategory) ? $this->model->image : '';
        $this->templateVars['editorPreviewImage'] = ($this->model instanceof ModelWithCategory) ? $this->model->previewImage : '';
        $this->templateVars['blurb'] = ($this->model instanceof ModelWithCategory) ? $this->model->blurb : '';

        $this->templateVars['internalLinks'] = $editorVariables->internalLinks;

        if (static::HAS_CATEGORY)
        {
            $this->templateVars['categories'] = Category::fetchAllAndSortByName();
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
            if ($this->model instanceof ModelWithCategory)
            {
                $showBreadcrumbs = $this->model->showBreadcrumbs;
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
