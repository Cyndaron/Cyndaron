<?php
declare(strict_types=1);

namespace Cyndaron\Editor;

use Cyndaron\Category\Category;
use Cyndaron\Page\Page;

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

    public int|null $id = null;

    public bool $useBackup = false;
    public string $content = '';
    public string $contentTitle = '';
    public string $template = 'Editor/PageBase';

    /** @var Category[] */
    public array $linkedCategories = [];

    abstract public function prepare(): void;
}
