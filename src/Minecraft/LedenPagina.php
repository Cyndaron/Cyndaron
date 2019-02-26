<?php
namespace Cyndaron\Minecraft;

use Cyndaron\Pagina;


class LedenPagina extends Pagina
{
    private $level = [
        "In de Goelag",
        "Aspirant-lid",
        "Lid",
        "Moderator",
        "Medebeheerder",
        "Eeuwige Dictator en Geliefde Leider van TXcraft",
    ];
    private $pageLevels = [
        'In de Goelag',
        'Aspirant-leden',
        'Leden',
        'Politbureau'
    ];

    public function __construct()
    {
        parent::__construct('Spelers');
        $this->addScript('/sys/js/mc-ledenpagina.js');
        $this->showPrePage();

        $spelers = $this->connection->query("SELECT * FROM mc_leden ORDER BY niveau DESC, mcnaam ASC");

        $tePreloaden = [];

        $lastLevel = 4;

        foreach ($spelers as $speler)
        {
            $highestLevel = count($this->pageLevels) - 1;
            $normalisedPageLevel = min($speler['niveau'], $highestLevel);

            for ($level = $highestLevel; $level >= 0; $level--)
            {
                if ($normalisedPageLevel == min($level, $highestLevel) &&
                    $lastLevel >= $level + 1)
                {
                    printf('<h2>%s</h2>', $this->pageLevels[$level]);
                    break;
                }
            }

            $lastLevel = $normalisedPageLevel;

            $vooraanzicht = "/minecraft/skin?vr=-10&amp;hr=20&amp;hrh=0&amp;vrla=-20&amp;vrra=20&amp;vrll=15&amp;vrrl=-10&amp;ratio=4&amp;format=png&amp;user={$speler['mcnaam']}";
            $achteraanzicht = "/minecraft/skin?vr=-10&amp;hr=200&amp;hrh=0&amp;vrla=-20&amp;vrra=20&amp;vrll=15&amp;vrrl=-10&amp;ratio=4&amp;format=png&amp;user={$speler['mcnaam']}";
            $tePreloaden[] = $achteraanzicht;

            echo '<div class="spelerswrapper">';
            echo '<table>';
            echo '<tr><td class="avatarbox">';

            echo '<img class="mc-speler-avatar" alt="Avatar van ' . $speler['echtenaam'] . '" title="Avatar van ' . $speler['echtenaam'] . '" src="' . $vooraanzicht . '" data-vooraanzicht="' . $vooraanzicht . '" data-achteraanzicht="' . $achteraanzicht . '" />';
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
                echo $this->level[$speler['niveau']];
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
                overflow: hidden;
            }

            .spelersnaam
            {
                font-family: "Trebuchet MS", Arial, sans-serif;
                font-size: 40px;
            }

            .avatarbox
            {
                width: 150px;
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

        $this->showPostPage();
    }
}