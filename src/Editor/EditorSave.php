<?php
declare(strict_types=1);

namespace Cyndaron\Editor;

use Cyndaron\Category\CategoryRepository;
use Cyndaron\Category\ModelWithCategory;
use Cyndaron\Category\ModelWithCategoryRepository;
use Cyndaron\DBAL\Connection;
use Cyndaron\FriendlyUrl\FriendlyUrlRepository;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Url\Url;
use Cyndaron\Url\UrlService;
use Cyndaron\Util\Util;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use function Safe\error_log;

abstract class EditorSave
{
    public const TYPE = '';

    protected string $returnUrl = '';

    public const PAGE_HEADER_DIR = Util::UPLOAD_DIR . '/images/page-header';
    public const PAGE_PREVIEW_DIR = Util::UPLOAD_DIR . '/images/page-preview';

    final public function updateFriendlyUrl(UrlService $urlService, Connection $connection, FriendlyUrlRepository $repository, int $id, string $friendlyUrl): void
    {
        // True if content does not support friendly URLs.
        if ($id <= 0)
        {
            return;
        }

        if ($friendlyUrl !== '')
        {
            $unfriendlyUrl = new Url('/' . static::TYPE . '/' . $id);
            $oldFriendlyUrl = (string)$urlService->toFriendly($unfriendlyUrl);
            $oldFriendlyUrlObj = $repository->fetchByName($oldFriendlyUrl);
            if ($oldFriendlyUrlObj !== null)
            {
                $repository->delete($oldFriendlyUrlObj);
            }
            $urlService->createFriendlyUrl($unfriendlyUrl, $friendlyUrl);
            // Als de friendly URL gebruikt is in het menu moet deze daar ook worden aangepast
            $connection->executeQuery('UPDATE menu SET link = ? WHERE link = ?', [$friendlyUrl, $oldFriendlyUrl]);
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

    /** @phpstan-ignore-next-line */
    protected function saveCategories(ModelWithCategoryRepository $repository, CategoryRepository $categoryRepository, ModelWithCategory $model, RequestParameters $post): void
    {
        foreach ($categoryRepository->fetchAll() as $category)
        {
            $selected = $post->getBool('category-' . $category->id);
            if ($selected)
            {
                $repository->linkToCategory($model, $category);
            }
            else
            {
                $repository->unlinkFromCategory($model, $category);
            }
        }
    }
}
