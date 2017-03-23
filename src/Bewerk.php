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
        $id = Request::geefGetVeilig('id');
        if ($id)
        {
            $this->id = intval($id);
        }
        else
        {
            $this->id = null;
        }

        $returnUrl = Request::geefGetVeilig('returnUrl');

        $this->prepare();

        if ($friendlyUrl = Request::geefPostVeilig('friendlyUrl'))
        {
            $unfriendlyUrl = new Url('toon' . $this->type . '.php?id=' . $this->id);
            $oudeFriendlyUrl = $unfriendlyUrl->geefFriendly();
            Url::verwijderFriendlyUrl($oudeFriendlyUrl);
            $unfriendlyUrl->maakFriendly($friendlyUrl);
            // Als de friendly URL gebruikt is in het menu moet deze daar ook worden aangepast
            DBConnection::geefEen('UPDATE menu SET link = ? WHERE link = ?', array($friendlyUrl, $oudeFriendlyUrl));
        }
        if (!$returnUrl)
        {
            $returnUrl = $_SESSION['referrer'];
            $returnUrl = strtr($returnUrl, array('&amp;' => '&'));
        }
        header('Location: ' . $returnUrl);
    }

    abstract protected function prepare();

    protected function parseTextForInlineImages($text)
    {
        return preg_replace_callback('/src="(data\:)(.*)"/', 'static::extractImages', $text);
    }

    protected static function extractImages($matches)
    {
        list($type, $image) = explode(';', $matches[2]);

        switch($type)
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

