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
        $this->showPrePage();

        if (User::isAdmin())
        {
            ?>
            <form method="post" action="/filecabinet/addItem" enctype="multipart/form-data">
                <label for="newFile">Bestand toevoegen:</label>
                <input type="file" id="newFile" name="newFile" required>
                <input type="hidden" name="csrfToken" value="<?=User::getCSRFToken('filecabinet', 'addItem')?>">
                <input class="btn btn-primary" type="submit" value="Uploaden">
            </form>
            <hr>
            <?php
        }

        // Introduction/comments
        $introduction = $this->getIntroduction();
        if ($introduction)
        {
            echo $introduction . '<hr>';
        }

        // File list
        if($bestandendir = @opendir(self::PATH))
        {
            $dirArray = [];

            while($filename = readdir($bestandendir))
            {
                // Hide hidden files, HTML files and PHP files
                if ((substr($filename, 0, 1) != '.') && (substr($filename, -4) != 'html') && (substr($filename, -3) != 'php'))
                {
                    $dirArray[] = $filename;
                }
            }
            closedir($bestandendir);

            if ($orderBy == 'date')
            {
                usort($dirArray, function ($file1, $file2) {
                    // In this order, because we want the newest files to come first.
                    return filectime(self::PATH . $file2) <=> filectime(self::PATH . $file1);
                });
            }
            else
            {
                natsort($dirArray);
            }

            echo '<ul>';
            $deleteCsrfToken = User::getCSRFToken('filecabinet', 'deleteItem');

            foreach ($dirArray as $filename)
            {
                echo '<li><a href="/bestandenkast/' . $filename . '">' . pathinfo($filename, PATHINFO_FILENAME) . '</a>';
                if (User::isAdmin())
                {
                    ?>
                    <form method="post" action="/filecabinet/deleteItem" style="display: inline">
                        <input type="hidden" name="csrfToken" value="<?=$deleteCsrfToken?>">
                        <input type="hidden" name="filename" value="<?=$filename?>">
                        <button class="btn btn-sm btn-danger" type="submit" title="Dit bestand verwijderen"><span class="glyphicon glyphicon-trash"></span></button>
                    </form>
                    <?php
                }
                echo '</li>';
            }

            echo '</ul>';
        }
        $this->showPostPage();
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