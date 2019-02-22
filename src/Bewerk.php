<?php
namespace Cyndaron;

require_once __DIR__ . '/../check.php';

abstract class Bewerk
{
    protected $id;
    protected $type = '';
    protected $returnUrl;

    public function __construct()
    {
        $id = Request::getVar(2);
        if ($id)
        {
            $this->id = intval($id);
        }
        else
        {
            $this->id = null;
        }

        $this->prepare();

        if ($friendlyUrl = Request::geefPostVeilig('friendlyUrl'))
        {
            $unfriendlyUrl = new Url('/' . $this->type . '/' . $this->id);
            $oudeFriendlyUrl = $unfriendlyUrl->geefFriendly();
            Url::verwijderFriendlyUrl($oudeFriendlyUrl);
            $unfriendlyUrl->maakFriendly($friendlyUrl);
            // Als de friendly URL gebruikt is in het menu moet deze daar ook worden aangepast
            DBConnection::doQueryAndFetchOne('UPDATE menu SET link = ? WHERE link = ?', [$friendlyUrl, $oudeFriendlyUrl]);
        }
        if (!$this->returnUrl)
        {
            $this->returnUrl = strtr($_SESSION['referrer'], ['&amp;' => '&']);
        }
        header('Location: ' . $this->returnUrl);
    }

    abstract protected function prepare();

    protected function parseTextForInlineImages($text)
    {
        return preg_replace_callback('/src="(data\:)(.*)"/', 'static::extractImages', $text);
    }

    protected static function extractImages($matches)
    {
        list($type, $image) = explode(';', $matches[2]);

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

        $image = str_replace('base64', '', $image);
        $image = base64_decode(str_replace(' ', '+', $image));
        $uploadDir = './afb/via-editor/';
        $destinationFilename = $uploadDir . date('c') . '-' . md5($image) . '.' . $extensie;
        @mkdir($uploadDir, 0777, true);
        file_put_contents($destinationFilename, $image);

        return 'src="' . $destinationFilename . '"';
    }
}

