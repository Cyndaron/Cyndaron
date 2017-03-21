<?php
require_once('functies.db.php');
require_once('pagina.php');

class MinecraftLedenPagina extends Pagina
{
	private $niveau = [
		"In de Goelag",
		"Aspirant-lid",
		"Lid",
		"Moderator",
		"Medebeheerder",
		"Eeuwige Dictator en Geliefde Leider van TXcraft",
	];

    public function __construct()
    {
        parent::__construct('Spelers');
        $this->toonPrePagina();

        $spelers = $this->connectie->query("SELECT * FROM mcleden ORDER BY niveau DESC, mcnaam ASC");

        $tePreloaden = [];

        $laatsteniveau = 0;

        foreach ($spelers as $speler)
        {
            if ($speler['niveau'] >= 3 && $laatsteniveau == 0)
                echo '<h2 style="border-bottom: 1px dotted;">Politbureau</h2>';
            if ($speler['niveau'] == 2 && $laatsteniveau >= 3)
                echo '<h2 style="border-bottom: 1px dotted;">Leden</h2>';
            if ($speler['niveau'] == 1 && $laatsteniveau >= 2)
                echo '<h2 style="border-bottom: 1px dotted;">Aspirant-leden</h2>';
            if ($speler['niveau'] == 0 && $laatsteniveau >= 1)
                echo '<h2 style="border-bottom: 1px dotted;">In de Goelag</h2>';
            $laatsteniveau = $speler['niveau'];
            $vooraanzicht = "sys/minecraft/cf-mcskin.php?vr=-10&hr=20&hrh=0&vrla=0&vrra=0&vrll=0&vrrl=0&ratio=4&format=png&displayHair=false&headOnly=false&user={$speler['mcnaam']}";
            $achteraanzicht = "sys/minecraft/cf-mcskin.php?vr=-10&hr=200&hrh=0&vrla=0&vrra=0&vrll=0&vrrl=0&ratio=4&format=png&displayHair=false&headOnly=false&user={$speler['mcnaam']}";
            $tePreloaden[] = $achteraanzicht;

            echo '<div style="display: inline-block; overflow:hidden;">';
            echo '<table>';
            echo '<tr><td style="width: 100px; padding: 10px 30px 10px 30px;">';

            echo '<img alt="Avatar van ' . $speler['echtenaam'] . '" title="Avatar van ' . $speler['echtenaam'] . '" src="' . $vooraanzicht . '" onmouseover="this.src=\'' . $achteraanzicht . '\'" onmouseout="this.src=\'' . $vooraanzicht . '\'" />';
            echo '</td>';
            echo '<td  style="width: 350px; padding: 10px 10px 10px 10px; vertical-align: middle;">';

            echo '<span style="font-family: Trebuchet MS, Arial, sans-serif; font-size: 40px;">' . $speler['mcnaam'] . '</span>';

            if ($speler['donateur'] == 1)
            {
                echo '<br /><span style="font-weight: bold; color: #B8860B;">Donateur</span>';
            }

            echo '<br />Echte naam: ' . $speler['echtenaam'];
            echo '<br />Status: ' . $speler['status'];

            if ($speler['niveau'] >= 3 && $speler['niveau'] <= 5)
            {
                echo '<br />Niveau: ';
                echo $this->niveau[$speler['niveau']];
            }

            echo '</td>';
            echo '</tr>';
            echo '</table>';
            echo '</div>';

            echo '<style type="text/css">body:after { content:';

            foreach ($tePreloaden as $image)
            {
                printf('url(%s) ', $image);
            }
            echo '; display: none;</style>';
        }

        $this->toonPostPagina();
    }
}

$pagina = new MinecraftLedenPagina();