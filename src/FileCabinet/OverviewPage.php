<?php
namespace Cyndaron\FileCabinet;

use Cyndaron\Page;
use Cyndaron\Setting;
use Cyndaron\User\User;

class OverviewPage extends Page
{
    private const PATH = './bestandenkast/';

    public function __construct()
    {
        $title = Setting::get('filecabinet_title') ?: 'Bestandenkast';
        $orderBy = Setting::get('filecabinet_orderBy') ?: 'name';
        parent::__construct($title);

        $dirArray = [];
        // File list
        if($bestandendir = @opendir(self::PATH))
        {

            while($filename = readdir($bestandendir))
            {
                // Hide hidden files, HTML files and PHP files
                if ((substr($filename, 0, 1) !== '.') && (substr($filename, -4) !== 'html') && (substr($filename, -3) !== 'php'))
                {
                    $dirArray[] = $filename;
                }
            }
            closedir($bestandendir);

            if ($orderBy === 'date')
            {
                usort($dirArray, static function ($file1, $file2) {
                    return filectime(self::PATH . $file1) <=> filectime(self::PATH . $file2);
                });
            }
            else
            {
                natsort($dirArray);
            }
        }

        $this->render([
            'introduction' => $this->getIntroduction(),
            'files' => $dirArray,
            'deleteCsrfToken' => User::getCSRFToken('filecabinet', 'deleteItem'),
        ]);
    }

    public function getIntroduction(): string
    {
        $introduction = '';

        $includefile = self::PATH . 'include.html';
        if ($handle = @fopen($includefile, 'r'))
        {
            $contents = fread($handle, filesize($includefile));
            fclose($handle);
            // Take the inner-HTML of the body, discarding the rest.
            preg_match("/<body(.*?)>(.*?)<\\/body>/si", $contents, $matches);
            $introduction = $matches[2];
        }

        return $introduction;
    }
}