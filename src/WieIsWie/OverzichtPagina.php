<?php
namespace Cyndaron\WieIsWie;

use Cyndaron\Pagina;

require_once __DIR__ . '/../../functies.db.php';

class OverzichtPagina extends Pagina
{
    public function __construct()
    {
        $connectie = newPDO();
        $leden = $connectie->query('SELECT * FROM leden ORDER BY achternaam,tussenvoegsel,voornaam;');
        parent::__construct('Wie is wie');
        $this->toonPrePagina();
        echo '<table class="ledenlijst">';
        foreach ($leden as $lid)
        {
            echo '<tr><td><img style="height: 150px;" alt="" src="afb/leden/' . $lid['foto'] . '"/></td>';
            echo '<td><b><span style="text-decoration: underline;">' . $lid['voornaam'] . ' ';
            echo $lid['tussenvoegsel'];
            if (substr($lid['tussenvoegsel'], -1) != "'")
                echo ' ';
            echo $lid['achternaam'] . '</span></b><br /><br />';
            echo $lid['functie'];

            toonIndienAanwezig($lid['opmerkingen'], '<br />', '');
            echo '</td></tr>';
        }
        echo '</table>';
        $this->toonPostPagina();
    }
}