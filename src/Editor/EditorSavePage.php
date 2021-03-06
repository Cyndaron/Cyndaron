<?php
namespace Cyndaron\Editor;

use Cyndaron\Category\Category;
use Cyndaron\DBAL\DBConnection;
use Cyndaron\FriendlyUrl\FriendlyUrl;
use Cyndaron\Category\ModelWithCategory;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Url;
use Cyndaron\Util\Util;

use function Safe\base64_decode;
use function Safe\date;
use function Safe\file_put_contents;
use function strtr;
use function preg_replace_callback;
use function is_string;
use function explode;
use function str_replace;
use function md5;
use function move_uploaded_file;

abstract class EditorSavePage
{
    public const TYPE = '';

    protected ?int $id = null;
    protected string $returnUrl = '';

    public const IMAGE_DIR = Util::UPLOAD_DIR . '/images/via-editor';
    public const PAGE_HEADER_DIR = Util::UPLOAD_DIR . '/images/page-header';
    public const PAGE_PREVIEW_DIR = Util::UPLOAD_DIR . '/images/page-preview';

    public function __construct(?int $id, RequestParameters $post)
    {
        $this->id = $id;

        $this->prepare($post);

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
            DBConnection::doQuery('UPDATE menu SET link = ? WHERE link = ?', [$friendlyUrl, $oldFriendlyUrl]);
        }
        if (!$this->returnUrl && isset($_SESSION['referrer']))
        {
            $this->returnUrl = strtr($_SESSION['referrer'], ['&amp;' => '&']);
        }
    }

    abstract protected function prepare(RequestParameters $post): void;

    protected function parseTextForInlineImages(string $text): string
    {
        /** @phpstan-ignore-next-line (false positive, callback _works_) */
        $result = preg_replace_callback('/src="(data:)([^"]*)"/', 'static::extractImages', $text);
        if (!is_string($result))
        {
            throw new \Exception('Error while parsing text for inline images!');
        }
        return $result;
    }

    protected static function extractImages(array $matches): string
    {
        [$type, $image] = explode(';', $matches[2]);

        switch ($type)
        {
            case 'image/gif':
                $extension = 'gif';
                break;
            case 'image/jpeg':
                $extension = 'jpg';
                break;
            case 'image/png':
                $extension = 'png';
                break;
            case 'image/bmp':
                $extension = 'bmp';
                break;
            default:
                return 'src="' . $matches[0] . '"';
        }

        $image = str_replace('base64,', '', $image);
        $image = base64_decode(str_replace(' ', '+', $image), true);
        $destinationFilename = self::IMAGE_DIR . '/' . date('c') . '-' . md5($image) . '.' . $extension;
        Util::createDir(self::IMAGE_DIR);
        file_put_contents($destinationFilename, $image);

        return 'src="' . Util::filenameToUrl($destinationFilename) . '"';
    }

    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }

    protected function saveHeaderAndPreviewImage(ModelWithCategory $model, RequestParameters $post): void
    {
        $image = $post->getUrl('image');
        if (!empty($_FILES['header-image-upload']['name']))
        {
            Util::ensureDirectoryExists(self::PAGE_HEADER_DIR);
            $filename = self::PAGE_HEADER_DIR . '/' . $_FILES['header-image-upload']['name'];
            if (move_uploaded_file($_FILES['header-image-upload']['tmp_name'], $filename))
            {
                $image = Util::filenameToUrl($filename);
            }
        }
        $previewImage = $post->getUrl('previewImage');
        if (!empty($_FILES['preview-image-upload']['name']))
        {
            Util::ensureDirectoryExists(self::PAGE_PREVIEW_DIR);
            $filename = self::PAGE_PREVIEW_DIR . '/' . $_FILES['preview-image-upload']['name'];
            if (move_uploaded_file($_FILES['preview-image-upload']['tmp_name'], $filename))
            {
                $previewImage = Util::filenameToUrl($filename);
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
