<?php
declare(strict_types=1);

namespace Cyndaron\Editor;

use Cyndaron\Category\CategoryRepository;
use Cyndaron\Category\ModelWithCategory;
use Cyndaron\Category\ModelWithCategoryRepository;
use Cyndaron\DBAL\Model;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Util\Util;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use function Safe\error_log;

abstract class EditorSave
{
    protected string $returnUrl = '';

    public const PAGE_HEADER_DIR = Util::UPLOAD_DIR . '/images/page-header';
    public const PAGE_PREVIEW_DIR = Util::UPLOAD_DIR . '/images/page-preview';

    abstract public function save(int|null $id): Model|null;

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
