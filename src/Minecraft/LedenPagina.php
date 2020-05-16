<?php
namespace Cyndaron\Minecraft;

use Cyndaron\DBConnection;
use Cyndaron\Page;


class LedenPagina extends Page
{
    private array $level = [
        'In de Goelag',
        'Aspirant-lid',
        'Lid',
        'Moderator',
        'Medebeheerder',
        'Eeuwige Dictator en Geliefde Leider van TXcraft',
    ];
    private array $pageLevels = [
        'In de Goelag',
        'Aspirant-leden',
        'Leden',
        'Politbureau'
    ];

    public function __construct()
    {
        parent::__construct('Spelers');
        $this->addScript('/src/Minecraft/js/memberpage.js');
        $this->addCss('/src/Minecraft/css/memberpage.min.css');

        $members = DBConnection::doQueryAndFetchAll('SELECT * FROM minecraft_members ORDER BY level DESC, userName ASC');

        $this->addTemplateVars([
            'members' => $members,
            'pageLevels' => $this->pageLevels,
            'level' => $this->level,
        ]);
    }
}