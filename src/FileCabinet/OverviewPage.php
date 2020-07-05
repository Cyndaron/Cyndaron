<?php
namespace Cyndaron\FileCabinet;

use Cyndaron\Page;
use Cyndaron\Setting;
use Cyndaron\User\User;
use Cyndaron\Util;

use Safe\Exceptions\DirException;
use Safe\Exceptions\FilesystemException;
use function Safe\fclose;
use function Safe\filectime;
use function Safe\filesize;
use function Safe\fopen;
use function Safe\fread;
use function Safe\natsort;
use function Safe\opendir;
use function Safe\preg_match;
use function Safe\substr;
use function Safe\usort;

final class OverviewPage extends Page
{
    private const PATH = Util::UPLOAD_DIR . '/filecabinet/';

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
        try
        {
            $handle = @fopen($includefile, 'rb');
            $contents = fread($handle, filesize($includefile));
            fclose($handle);
            // Take the inner-HTML of the body, discarding the rest.
            preg_match("/<body(.*?)>(.*?)<\\/body>/si", $contents, $matches);
            $introduction = $matches[2];
        }
        catch (FilesystemException $e)
        {
        }

        return $introduction;
    }

    /**
     * @param string $orderBy
     * @return array
     */
    private function getFileList(string $orderBy): array
    {
        try
        {
            $dir = @opendir(self::PATH);
        }
        catch (DirException $e)
        {
            return [];
        }

        $dirArray = [];
        while ($filename = readdir($dir))
        {
            // Hide hidden files, HTML files and PHP files
            if ((substr($filename, 0, 1) !== '.') && (substr($filename, -4) !== 'html') && (substr($filename, -3) !== 'php'))
            {
                $dirArray[] = $filename;
            }
        }
        closedir($dir);

        $this->sortFileList($dirArray, $orderBy);
        return $dirArray;
    }

    private function sortFileList(array &$fileList, string $orderBy): void
    {
        if ($orderBy === 'date')
        {
            usort($fileList, static function($file1, $file2)
            {
                return filectime(self::PATH . $file1) <=> filectime(self::PATH . $file2);
            });
        }
        else
        {
            natsort($fileList);
        }
    }
}
