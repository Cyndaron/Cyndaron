<?php
declare (strict_types = 1);

namespace Cyndaron\Editor;

use Cyndaron\Controller;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;

class EditorController extends Controller
{
    protected $minLevelGet = UserLevel::ADMIN;

    protected $editorPages = [
        'category' => \Cyndaron\Category\EditorPage::class,
        'mailform' => \Cyndaron\Mailform\EditorPage::class,
        'photo' => \Cyndaron\EditorFoto::class,
        'photoalbum' => \Cyndaron\EditorFotoalbum::class,
        'sub' => \Cyndaron\StaticPage\EditorPage::class,
    ];
    protected $savePages = [
        'category' => \Cyndaron\BewerkCategorie::class,
        'photo' => \Cyndaron\BewerkFoto::class,
        'photoalbum' => \Cyndaron\BewerkFotoalbum::class,
        'sub' => \Cyndaron\BewerkStatischePagina::class,
    ];

    public function routeGet()
    {
        $type = Request::getVar(1);
        if (array_key_exists($type, $this->editorPages))
        {
            $class = $this->editorPages[$type];
            new $class;
        }
    }

    public function routePost()
    {
        $type = Request::getVar(1);
        if (array_key_exists($type, $this->savePages))
        {
            $class = $this->savePages[$type];
            new $class;
        }
    }

}