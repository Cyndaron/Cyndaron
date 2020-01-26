<?php
namespace Cyndaron\Minecraft;

use Cyndaron\DBConnection;
use Cyndaron\Page;


class LedenPagina extends Page
{
    private array $level = [
        "In de Goelag",
        "Aspirant-lid",
        "Lid",
        "Moderator",
        "Medebeheerder",
        "Eeuwige Dictator en Geliefde Leider van TXcraft",
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
        $this->addScript('/sys/js/mc-ledenpagina.js');
        $this->addCss('/sys/css/minecraft-members.min.css');

        $members = DBConnection::doQueryAndFetchAll('SELECT * FROM minecraft_members ORDER BY level DESC, userName ASC');

        $this->render([
            'members' => $members,
            'pageLevels' => $this->pageLevels,
            'level' => $this->level,
        ]);
    }
}