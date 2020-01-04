<?php
namespace Cyndaron\User;

use Cyndaron\DBConnection;
use Cyndaron\Page;


class Gallery extends Page
{
    const FALLBACK_IMAGE = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    public function __construct()
    {
        $members = User::fetchAll(['hideFromMemberList = 0'], [], 'ORDER BY lastname, tussenvoegsel, firstName');
        parent::__construct('Wie is wie');

        $this->render([
            'members' => $members,
            'fallbackImage' => self::FALLBACK_IMAGE,
        ]);
    }
}