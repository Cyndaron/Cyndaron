<?php
namespace Cyndaron\Minecraft;

use Cyndaron\DBConnection;
use Cyndaron\Page;


class LedenPagina extends Page
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
        $this->addCss('/sys/css/minecraft-members.min.css');
        $this->showPrePage();

        $members = DBConnection::doQueryAndFetchAll('SELECT * FROM minecraft_members ORDER BY level DESC, userName ASC');

        $tePreloaden = [];

        $lastLevel = 4;

        foreach ($members as $member)
        {
            $highestLevel = count($this->pageLevels) - 1;
            $normalisedPageLevel = min($member['level'], $highestLevel);

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

            $frontView = "/minecraft/skin?vr=-10&amp;hr=20&amp;hrh=0&amp;vrla=-20&amp;vrra=20&amp;vrll=15&amp;vrrl=-10&amp;ratio=4&amp;format=png&amp;user={$member['userName']}";
            $backView = "/minecraft/skin?vr=-10&amp;hr=200&amp;hrh=0&amp;vrla=-20&amp;vrra=20&amp;vrll=15&amp;vrrl=-10&amp;ratio=4&amp;format=png&amp;user={$member['userName']}";
            $tePreloaden[] = $backView;

            echo '<div class="spelerswrapper">';
            echo '<table>';
            echo '<tr><td class="avatarbox">';

            echo '<img class="mc-speler-avatar" alt="Avatar van ' . $member['realName'] . '" title="Avatar van ' . $member['realName'] . '" src="' . $frontView . '" data-vooraanzicht="' . $frontView . '" data-achteraanzicht="' . $backView . '" />';
            echo '</td>';
            echo '<td class="spelersinfobox">';

            echo '<span class="spelersnaam">' . $member['userName'] . '</span>';

            if ($member['donor'] == 1)
            {
                echo '<br /><span class="donor">Donateur</span>';
            }

            echo '<br />Echte naam: ' . $member['realName'];
            echo '<br />Status: ' . $member['status'];

            if ($member['level'] >= 3 && $member['level'] <= 5)
            {
                echo '<br />Niveau: ';
                echo $this->level[$member['level']];
            }

            echo '</td>';
            echo '</tr>';
            echo '</table>';
            echo '</div>';
        }

        $this->templateVars['preloadLinks'] = $tePreloaden;

        $this->showPostPage();
    }
}