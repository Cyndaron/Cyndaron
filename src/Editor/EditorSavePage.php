<?php
namespace Cyndaron\Editor;

use Cyndaron\Category\Category;
use Cyndaron\Category\ModelWithCategory;
use Cyndaron\DBAL\DBConnection;
use Cyndaron\FriendlyUrl\FriendlyUrl;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Url\Url;
use Cyndaron\Util\Util;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use function Safe\error_log;

abstract class EditorSavePage
{
    public const TYPE = '';

    protected string $returnUrl = '';

    public const PAGE_HEADER_DIR = Util::UPLOAD_DIR . '/images/page-header';
    public const PAGE_PREVIEW_DIR = Util::UPLOAD_DIR . '/images/page-preview';

    public function updateFriendlyUrl(int $id, string $friendlyUrl): void
    {
        // True if content does not support friendly URLs.
        if ($id <= 0)
        {
            return;
        }

        if ($friendlyUrl !== '')
        {
            $unfriendlyUrl = new Url('/' . static::TYPE . '/' . $id);
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
        // TODO oude URL verwijderen
    }

    abstract public function save(int|null $id): int;

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
