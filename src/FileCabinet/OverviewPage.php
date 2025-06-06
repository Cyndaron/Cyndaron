<?php
namespace Cyndaron\FileCabinet;

use Cyndaron\Page\Page;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\Util\Setting;
use Cyndaron\Util\SettingsRepository;
use Cyndaron\Util\Util;
use ErrorException;
use Safe\Exceptions\DirException;
use function closedir;
use function natsort;
use function readdir;
use function Safe\fclose;
use function Safe\filectime;
use function Safe\filesize;
use function Safe\fopen;
use function Safe\fread;
use function Safe\opendir;
use function Safe\preg_match;
use function Safe\preg_replace;
use function substr;
use function usort;

final class OverviewPage extends Page
{
    private const PATH = Util::UPLOAD_DIR . '/filecabinet/';

    public function __construct(CSRFTokenHandler $tokenHandler, SettingsRepository $sr)
    {
        $title = $sr->get('filecabinet_title') ?: 'Bestandenkast';
        $orderBy = $sr->get('filecabinet_orderBy') ?: 'name';
        $this->title = $title;

        $this->addTemplateVars([
            'introduction' => $this->getIntroduction(),
            'files' => $this->getFileList($orderBy),
            'addItemToken' => $tokenHandler->get('filecabinet', 'addItem'),
            'deleteCsrfToken' => $tokenHandler->get('filecabinet', 'deleteItem'),
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
            $introduction = $matches[2] ?? '';
            // Strip style attributes
            $introduction = preg_replace('/\bstyle="(.*?)"/i', '', $introduction);
            // Strip font tags (but keep their contents)
            $introduction = preg_replace('/<font(.*?)>/', '', $introduction);
            $introduction = preg_replace('/<\/font(.*?)>/', '', $introduction);
        }
        catch (ErrorException)
        {
        }

        /** @var string $introduction */
        return $introduction;
    }

    /**
     * @param string $orderBy
     * @throws \Safe\Exceptions\StringsException
     * @return string[]
     */
    private function getFileList(string $orderBy): array
    {
        try
        {
            $dir = @opendir(self::PATH);
        }
        catch (DirException)
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

    /**
     * @param string[] $fileList
     * @param string $orderBy
     * @throws \Safe\Exceptions\FilesystemException
     * @return void
     */
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
