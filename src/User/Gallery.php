<?php
namespace Cyndaron\User;

use Cyndaron\DBConnection;
use Cyndaron\Page;


class Gallery extends Page
{
    const FALLBACK_IMAGE = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    public function __construct()
    {
        $leden = User::fetchAll(['hideFromMemberList = 0'], [], 'ORDER BY lastname, tussenvoegsel, firstName');
        parent::__construct('Wie is wie');
        $this->showPrePage();
        echo '<table class="ledenlijst">';
        foreach ($leden as $lid)
        {
            $avatar = $lid->avatar ? '/' . User::AVATAR_DIR . "/{$lid->avatar}" : static::FALLBACK_IMAGE;

            echo '<tr><td><img style="height: 150px;" alt="" src="' . $avatar . '"/></td>';
            echo '<td><b><span style="text-decoration: underline;">';
            if ($lid->firstName || $lid->lastName)
            {
                echo $lid->getFullName();
            }
            else
            {
                echo $lid->username;
            }
            echo '</span></b><br /><br />';
            echo $lid->role;

            static::showIfSet($lid->comments, '<br />', '');
            echo '</td></tr>';
        }
        echo '</table>';
        $this->showPostPage();
    }
}