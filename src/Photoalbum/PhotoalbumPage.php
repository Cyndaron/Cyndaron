<?php
declare (strict_types = 1);

namespace Cyndaron\Photoalbum;

use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\User\User;
use Cyndaron\Widget\Button;

class PhotoalbumPage extends Page
{
    public function __construct(int $id)
    {
        if ($id < 1)
        {
            header("Location: /error/404");
            die('Incorrecte parameter ontvangen.');
        }
        $this->model = new Photoalbum($id);
        $this->model->load();

        // FIXME: Is this necessary, and why specifically here?
        $_SESSION['referrer'] = !empty($_SERVER['HTTP_REFERER']) ? htmlentities($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8') : '';

        $controls = new Button('edit', '/editor/photoalbum/' . $id, 'Dit fotoalbum bewerken');
        parent::__construct($this->model->name);
        $this->setTitleButtons((string)$controls);
        $this->addScript('/sys/js/lightbox.min.js');
        $this->showPrePage();

        if ($dirArray = @scandir("./fotoalbums/$id"))
        {
            natsort($dirArray);

            $numEntries = 0;

            $output = '<div class="fotoalbum">';

            for ($index = 0; $index < count($dirArray); $index++)
            {
                if (substr($dirArray[$index], 0, 1) != ".")
                {
                    $numEntries++;

                    $fotoLink = 'fotoalbums/' . $id . '/' . $dirArray[$index];
                    $thumbnailLink = 'fotoalbums/' . $id . 'thumbnails/' . $dirArray[$index];
                    $hash = md5_file($fotoLink);
                    $dataTitleTag = '';
                    if ($caption = DBConnection::doQueryAndFetchOne('SELECT caption FROM photoalbum_captions WHERE hash=?', [$hash]))
                    {
                        // Vervangen van aanhalingstekens is nodig omdat er links in de beschrijving kunnen zitten.
                        $dataTitleTag = 'data-title="' . str_replace('"', '&quot;', $caption) . '"';
                    }

                    $output .= sprintf('<div class="fotobadge"><a href="/%s" data-lightbox="%s" %s data-hash="%s"><img class="thumb" src="/fotoalbums/%d', $fotoLink, htmlspecialchars($this->model->name), $dataTitleTag, $hash, $id);

                    if (file_exists($thumbnailLink))
                    {
                        $output .= 'thumbnails/' . $dirArray[$index] . '"';
                    }
                    else
                    {
                        $output .= '/' . $dirArray[$index] . '" style="width:270px; height:200px"';
                    }
                    $output .= ' alt="' . $dirArray[$index] . '" /></a>';
                    if (User::isAdmin())
                    {
                        $output .= '<br>' . new Button('edit', '/editor/photo/' . $hash, 'Bijschrift bewerken', 'Bijschrift bewerken', 16);
                    }
                    $output .= '</div>';

                }
            }
            $output .= '</div>';

            static::showIfSet($this->model->notes);
            if ($numEntries === 1)
            {
                echo "Dit album bevat 1 foto. Klik op de verkleinde foto om een vergroting te zien.";
            }
            else
            {
                echo "Dit album bevat $numEntries foto's. Klik op de verkleinde foto's om een vergroting te zien.";
            }

            echo '<br /><br />';
            echo $output;
        }
        else
        {
            echo 'Dit album is leeg.<br />';
        }
        $this->showPostPage();
    }
}