<?php
namespace Cyndaron\Editor;

use Cyndaron\DBConnection;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Url;
use Cyndaron\Util;

abstract class EditorSavePage
{
    public const TYPE = '';

    protected ?int $id = null;
    protected string $returnUrl = '';

    public function __construct(?int $id, RequestParameters $post)
    {
        $this->id = $id;

        $this->prepare($post);

        if ($friendlyUrl = $post->getUrl('friendlyUrl'))
        {
            $unfriendlyUrl = new Url('/' . static::TYPE . '/' . $this->id);
            $oudeFriendlyUrl = $unfriendlyUrl->getFriendly();
            Url::deleteFriendlyUrl($oudeFriendlyUrl);
            $unfriendlyUrl->createFriendly($friendlyUrl);
            // Als de friendly URL gebruikt is in het menu moet deze daar ook worden aangepast
            DBConnection::doQuery('UPDATE menu SET link = ? WHERE link = ?', [$friendlyUrl, $oudeFriendlyUrl]);
        }
        if (!$this->returnUrl && isset($_SESSION['referrer']))
        {
            $this->returnUrl = strtr($_SESSION['referrer'], ['&amp;' => '&']);
        }
    }

    abstract protected function prepare(RequestParameters $post);

    protected function parseTextForInlineImages(string $text): string
    {
        $result = preg_replace_callback('/src="(data:)([^"]*)"/', 'static::extractImages', $text);
        if (!is_string($result))
        {
            throw new \Exception('Error while parsing text for inline images!');
        }
        return $result;
    }

    protected static function extractImages($matches): string
    {
        [$type, $image] = explode(';', $matches[2]);

        switch ($type)
        {
            case 'image/gif':
                $extensie = 'gif';
                break;
            case 'image/jpeg':
                $extensie = 'jpg';
                break;
            case 'image/png':
                $extensie = 'png';
                break;
            case 'image/bmp':
                $extensie = 'bmp';
                break;
            default:
                return 'src="' . $matches[0] . '"';
        }

        $image = str_replace('base64,', '', $image);
        $image = base64_decode(str_replace(' ', '+', $image), true);
        $uploadDir = 'uploads/images/via-editor/';
        $destinationFilename = $uploadDir . date('c') . '-' . md5($image) . '.' . $extensie;
        Util::createDir($uploadDir);
        file_put_contents($destinationFilename, $image);

        return 'src="/' . $destinationFilename . '"';
    }

    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }
}
