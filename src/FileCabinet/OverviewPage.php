<?php
namespace Cyndaron\Bestandenkast;

use Cyndaron\Page;

class OverviewPage extends Page
{
    public function __construct()
    {
        parent::__construct('Oefenbestanden');
        $this->showPrePage();

        // Introduction/comments
        $includefile = './bestandenkast/include.html';
        if ($handle = @fopen($includefile, 'r'))
        {
            $contents = fread($handle, filesize($includefile));
            // Take the inner-HTML of the body, discarding the rest.
            preg_match("/<body(.*?)>(.*?)<\\/body>/si", $contents, $match);
            echo $match[2];
            fclose($handle);
            echo '<hr />';
        }

        // File list
        if($bestandendir = @opendir("./bestandenkast"))
        {
            $dirArray = [];

            while($entryName = readdir($bestandendir))
            {
                $dirArray[] = $entryName;
            }
            closedir($bestandendir);
            $indexCount = count($dirArray);
            natsort($dirArray);


            echo '<ul>';

            for($index = 0; $index < $indexCount; $index++)
            {
                // Hide hidden files, HTML files and PHP files
                if ((substr("$dirArray[$index]", 0, 1) != ".") && (substr("$dirArray[$index]", -4) != "html") && (substr("$dirArray[$index]", -3) != "php"))
                {
                    echo '<li><a href="/bestandenkast/'.$dirArray[$index].'">'.pathinfo($dirArray[$index], PATHINFO_FILENAME).'</a></li>';
                }
            }
            echo '</ul>';
        }
        $this->showPostPage();
    }
}