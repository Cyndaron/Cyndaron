<?php
declare (strict_types = 1);

namespace Cyndaron\Photoalbum;

use Cyndaron\Page;
use Cyndaron\User\User;
use Cyndaron\Widget\Button;

class PhotoalbumPage extends Page
{
    public function __construct(int $id)
    {
        if ($id < 1)
        {
            header('Location: /error/404');
            die('Incorrecte parameter ontvangen.');
        }
        $this->model = new Photoalbum($id);
        $this->model->load();

        $controls = new Button('edit', '/editor/photoalbum/' . $id, 'Dit fotoalbum bewerken');
        parent::__construct($this->model->name);
        $this->setTitleButtons((string)$controls);
        $this->addScript('/sys/js/lightbox.min.js');
        $numEntries = 0;
        $photos = [];

        if ($dirArray = @scandir("./fotoalbums/$id"))
        {
            natsort($dirArray);

            for ($index = 0; $index < count($dirArray); $index++)
            {
                if (substr($dirArray[$index], 0, 1) != ".")
                {
                    $numEntries++;

                    $link = 'fotoalbums/' . $id . '/' . $dirArray[$index];
                    $thumbnailLink = 'fotoalbums/' . $id . 'thumbnails/' . $dirArray[$index];
                    $hash = md5_file($link);
                    $dataTitleTag = '';
                    $captionObj = PhotoalbumCaption::loadByHash($hash);
                    $captionId = $captionObj ? $captionObj->id : 0;

                    if ($captionObj->caption)
                    {
                        // Vervangen van aanhalingstekens is nodig omdat er links in de beschrijving kunnen zitten.
                        $dataTitleTag = 'data-title="' . str_replace('"', '&quot;', $captionObj->caption) . '"';
                    }

                    $photos[] = compact( 'link', 'thumbnailLink', 'hash', 'dataTitleTag', 'captionObj', 'captionId') + ['filename' => $dirArray[$index]];

                }
            }
        }

        $this->twigVars['albumId'] = $id;
        $this->twigVars['numEntries'] = $numEntries;
        $this->twigVars['model'] = $this->model;
        $this->twigVars['photos'] = $photos;
    }
}