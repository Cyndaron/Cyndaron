<?php
namespace Cyndaron\User;

use Cyndaron\Page;
use Cyndaron\Setting;

class Gallery extends Page
{
    private const FALLBACK_IMAGE = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    public function __construct()
    {
        $members = User::fetchAll(['hideFromMemberList = 0'], [], 'ORDER BY lastname, tussenvoegsel, firstName');
        $title = Setting::get('userGalleryTitle') ?: 'Wie is wie';
        parent::__construct($title);

        $this->render([
            'members' => $members,
            'fallbackImage' => self::FALLBACK_IMAGE,
        ]);
    }
}