<?php
namespace Cyndaron\Kaartverkoop;

use Cyndaron\DBConnection;

class GereserveerdePlaatsen
{
    public function __construct(int $concert_id)
    {
        $connectie = DBConnection::getPDO();

        $bezette_plaatsen_per_rij = [];

        $prep = $connectie->prepare('SELECT * FROM kaartverkoop_gereserveerde_plaatsen WHERE bestelling_id IN (SELECT id FROM kaartverkoop_bestellingen WHERE concert_id=?)');
        $prep->execute([$concert_id]);
        $bezette_plaatsen_rijen = $prep->fetchAll();

        foreach ($bezette_plaatsen_rijen as $bezette_plaatsen)
        {
            $prep = $connectie->prepare('SELECT * FROM kaartverkoop_bestellingen WHERE id=?');
            $prep->execute([$bezette_plaatsen['bestelling_id']]);
            $bestelling = $prep->fetch();

            for ($i = $bezette_plaatsen['eerste_stoel']; $i <= $bezette_plaatsen['laatste_stoel']; $i++)
            {
                $bezette_plaatsen_per_rij[$bezette_plaatsen['rij']][$i] = $bestelling['voorletters'] . ' ' . $bestelling['achternaam'] . ' (' . $bestelling['id'] . ')';
            }
        }

        #$rijen = range('A', 'P');
        #$stoelen_per_rij = range(1, 15);
        $rijen = ['A'];
        $stoelen_per_rij = range(1, Util::MAX_RESERVED_SEATS);

//        echo '<table><thead><tr><th>&nbsp;</th>';
//        foreach ($stoelen_per_rij as $stoel)
//        {
//            printf('<th>%d</th>', (int)$stoel);
//        }

        //echo '</tr></thead><tbody>';

        foreach ($rijen as $rij)
        {
            //printf('<tr><th>%s</th>', $rij);

            foreach ($stoelen_per_rij as $stoel)
            {
                echo '<div style="display: inline-block; text-align: center; width: 220px; margin: 5px;">' . $stoel . '<br>';
                if (isset($bezette_plaatsen_per_rij[$rij][$stoel]))
                {
                    //printf('<td style="text-align: center;">%s</td>', $bezette_plaatsen_per_rij[$rij][$stoel]);
                    echo $bezette_plaatsen_per_rij[$rij][$stoel];
                }
                else
                {
                    //echo '<td style="text-align: center;">&nbsp;</td>';
                    echo '&nbsp;';
                }
                echo '</div>';
            }

            //echo '</tr>';
        }

    }
}