<?php
namespace Cyndaron\User;

use Cyndaron\DBConnection;
use Cyndaron\Pagina;


class Gallery extends Pagina
{
    const FALLBACK_IMAGE = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    public function __construct()
    {
        $leden = DBConnection::doQueryAndFetchAll('SELECT * FROM users ORDER BY lastname, tussenvoegsel, firstname;');
        parent::__construct('Wie is wie');
        $this->showPrePage();
        echo '<table class="ledenlijst">';
        foreach ($leden as $lid)
        {
            $avatar = $lid['avatar'] ? 'afb/leden/' . $lid['avatar'] : static::FALLBACK_IMAGE;

            echo '<tr><td><img style="height: 150px;" alt="" src="' . $avatar . '"/></td>';
            echo '<td><b><span style="text-decoration: underline;">';
            if ($lid['firstname'] || $lid['lastname'])
            {
                echo $lid['firstname'] . ' ';
                echo $lid['tussenvoegsel'];
                if (substr($lid['tussenvoegsel'], -1) != "'")
                    echo ' ';
                echo $lid['lastname'];
            }
            else
            {
                echo $lid['username'];
            }
            echo '</span></b><br /><br />';
            echo $lid['role'];

            static::showIfSet($lid['comments'], '<br />', '');
            echo '</td></tr>';
        }
        echo '</table>';
        $this->showPostPage();
    }
}