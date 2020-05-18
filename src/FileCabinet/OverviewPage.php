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

        $this->addTemplateVars([
            'introduction' => $this->getIntroduction(),
            'files' => $this->getFileList($orderBy),
            'deleteCsrfToken' => User::getCSRFToken('filecabinet', 'deleteItem'),
        ]);
    }

    public function getIntroduction(): string
    {
        $introduction = '';

        $includefile = self::PATH . 'include.html';
        if ($handle = @fopen($includefile, 'rb'))
        {
            $contents = fread($handle, filesize($includefile));
            fclose($handle);
            // Take the inner-HTML of the body, discarding the rest.
            preg_match("/<body(.*?)>(.*?)<\\/body>/si", $contents, $matches);
            $introduction = $matches[2];
        }

        return $introduction;
    }

    /**
     * @param string $orderBy
     * @return array
     */
    private function getFileList(string $orderBy): array
    {
        $dirArray = [];
        if ($dir = @opendir(self::PATH))
        {
            while ($filename = readdir($dir))
            {
                // Hide hidden files, HTML files and PHP files
                if ((substr($filename, 0, 1) !== '.') && (substr($filename, -4) !== 'html') && (substr($filename, -3) !== 'php'))
                {
                    $dirArray[] = $filename;
                }
            }
            closedir($dir);

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
        return $dirArray;
    }
}