<?php
namespace Cyndaron\Minecraft;

use Cyndaron\Pagina;


class LedenPagina extends Pagina
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

        $spelers = $this->connectie->query("SELECT * FROM mc_leden ORDER BY niveau DESC, mcnaam ASC");

        $tePreloaden = [];

        $laatsteniveau = 0;

        foreach ($spelers as $speler)
        {
            if ($speler['niveau'] >= 3 && $laatsteniveau == 0)
                echo '<h2>Politbureau</h2>';
            if ($speler['niveau'] == 2 && $laatsteniveau >= 3)
                echo '<h2>Leden</h2>';
            if ($speler['niveau'] == 1 && $laatsteniveau >= 2)
                echo '<h2>Aspirant-leden</h2>';
            if ($speler['niveau'] == 0 && $laatsteniveau >= 1)
                echo '<h2>In de Goelag</h2>';
            $laatsteniveau = $speler['niveau'];
            $displayHair = ($speler['renderAvatarHaar'] == 1) ? 'true' : 'false';
            $vooraanzicht = "mc-skinrenderer?vr=-10&amp;hr=20&amp;hrh=0&amp;vrla=-20&amp;vrra=20&amp;vrll=15&amp;vrrl=-10&amp;ratio=4&amp;format=png&amp;displayHair={$displayHair}&amp;headOnly=false&amp;user={$speler['mcnaam']}";
            $achteraanzicht = "mc-skinrenderer?vr=-10&amp;hr=200&amp;hrh=0&amp;vrla=-20&amp;vrra=20&amp;vrll=15&amp;vrrl=-10&amp;ratio=4&amp;format=png&amp;displayHair={$displayHair}&amp;headOnly=false&amp;user={$speler['mcnaam']}";
            $tePreloaden[] = $achteraanzicht;

            echo '<div class="spelerswrapper">';
            echo '<table>';
            echo '<tr><td class="avatarbox">';

            echo '<img alt="Avatar van ' . $speler['echtenaam'] . '" title="Avatar van ' . $speler['echtenaam'] . '" src="' . $vooraanzicht . '" onmouseover="this.src=\'' . $achteraanzicht . '\'" onmouseout="this.src=\'' . $vooraanzicht . '\'" />';
            echo '</td>';
            echo '<td class="spelersinfobox">';

            echo '<span class="spelersnaam">' . $speler['mcnaam'] . '</span>';

            if ($speler['donateur'] == 1)
            {
                echo '<br /><span class="donateur">Donateur</span>';
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
        }
        $preloadUrls = '';

        foreach ($tePreloaden as $image)
        {
            $preloadUrls .= sprintf('url(%s) ', str_replace('&amp;', '&', $image));
        }
        ?>
        <style type="text/css">
            h2
            {
                border-bottom: 1px dotted;
            }
            .spelerswrapper
            {
                display: inline-block;
                overflow:hidden;
            }
            .spelersnaam
            {
                font-family: "Trebuchet MS", Arial, sans-serif;
                font-size: 40px;
            }
            .avatarbox
            {
                width: 100px;
                padding: 10px 30px 10px 30px;
            }
            .spelersinfobox
            {
                width: 350px;
                padding: 10px 10px 10px 10px;
                vertical-align: middle;
            }
            .donateur
            {
                font-weight: bold;
                color: #B8860B;
            }
            body:after
            {
                content: <?=$preloadUrls;?>;
                display: none;
            }
        </style>
        <?php

        $this->toonPostPagina();
    }
}