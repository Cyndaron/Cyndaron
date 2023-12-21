<?php
namespace Cyndaron\Editor;

use Cyndaron\Category\Category;
use Cyndaron\DBAL\DBConnection;
use Cyndaron\FriendlyUrl\FriendlyUrl;
use Cyndaron\Category\ModelWithCategory;
use Cyndaron\Imaging\ImageExtractor;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Url;
use Cyndaron\Util\Filetypes;
use Cyndaron\Util\Util;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use function Safe\base64_decode;
use function Safe\date;
use function Safe\error_log;
use function Safe\file_put_contents;
use function strtr;
use function preg_replace_callback;
use function is_string;
use function explode;
use function str_replace;
use function md5;

abstract class EditorSavePage
{
    public const TYPE = '';

    protected int|null $id = null;
    protected string $returnUrl = '';

    public const IMAGE_DIR = Util::UPLOAD_DIR . '/images/via-editor';
    public const PAGE_HEADER_DIR = Util::UPLOAD_DIR . '/images/page-header';
    public const PAGE_PREVIEW_DIR = Util::UPLOAD_DIR . '/images/page-preview';
    protected readonly ImageExtractor $imageExtractor;

    public function __construct(int|null $id, RequestParameters $post, Request $request)
    {
        $this->id = $id;
        $this->imageExtractor = new ImageExtractor(self::IMAGE_DIR);

        $this->prepare($post, $request);

        $friendlyUrl = $post->getUrl('friendlyUrl');
        if ($friendlyUrl !== '')
        {
            $unfriendlyUrl = new Url('/' . static::TYPE . '/' . $this->id);
            $oldFriendlyUrl = $unfriendlyUrl->getFriendly();
            $oldFriendlyUrlObj = FriendlyUrl::fetchByName($oldFriendlyUrl);
            if ($oldFriendlyUrlObj !== null)
            {
                $oldFriendlyUrlObj->delete();
            }
            $unfriendlyUrl->createFriendly($friendlyUrl);
            // Als de friendly URL gebruikt is in het menu moet deze daar ook worden aangepast
            DBConnection::getPDO()->executeQuery('UPDATE menu SET link = ? WHERE link = ?', [$friendlyUrl, $oldFriendlyUrl]);
        }
        if (!$this->returnUrl && isset($_SESSION['referrer']))
        {
            $this->returnUrl = strtr($_SESSION['referrer'], ['&amp;' => '&']);
        }
    }

    abstract protected function prepare(RequestParameters $post, Request $request): void;

    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }

    protected function saveHeaderAndPreviewImage(ModelWithCategory $model, RequestParameters $post, Request $request): void
    {
        Util::ensureDirectoryExists(self::PAGE_HEADER_DIR);
        Util::ensureDirectoryExists(self::PAGE_PREVIEW_DIR);

        $files = $request->files;
        $image = $post->getUrl('editorHeaderImage');
        $headerImageFile = $files->get('header-image-upload');
        if ($headerImageFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile)
        {
            try
            {
                $file = $headerImageFile->move(self::PAGE_HEADER_DIR, $headerImageFile->getClientOriginalName());
                $image = Util::filenameToUrl($file->getPathname());
            }
            catch (FileException $e)
            {
                error_log((string)$e);
            }
        }
        $previewImage = $post->getUrl('editorPreviewImage');
        $previewImageFile = $files->get('preview-image-upload');
        if ($previewImageFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile)
        {
            try
            {
                $file = $previewImageFile->move(self::PAGE_PREVIEW_DIR, $previewImageFile->getClientOriginalName());
                $previewImage = Util::filenameToUrl($file->getPathname());
            }
            catch (FileException $e)
            {
                error_log((string)$e);
            }
        }
        $model->image = $image;
        $model->previewImage = $previewImage;
    }

    protected function saveCategories(ModelWithCategory $model, RequestParameters $post): void
    {
        foreach (Category::fetchAll() as $category)
        {
            $selected = $post->getBool('category-' . $category->id);
            if ($selected)
            {
                $model->addCategory($category);
            }
            else
            {
                $model->removeCategory($category);
            }
        }
    }
}
