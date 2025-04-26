<?php
namespace Cyndaron\Minecraft\Member;

use Cyndaron\Page\Page;

final class MembersPage extends Page
{
    private const LEVELS = [
        'In de Goelag',
        'Aspirant-lid',
        'Lid',
        'Moderator',
        'Medebeheerder',
        'Eeuwige Dictator en Geliefde Leider van TXcraft',
    ];
    private const PAGE_LEVELS = [
        'In de Goelag',
        'Aspirant-leden',
        'Leden',
        'Politbureau'
    ];

    public function __construct(MemberRepository $memberRepository)
    {
        $this->title = 'Spelers';
        $this->addScript('/src/Minecraft/js/MembersPage.js');
        $this->addCss('/src/Minecraft/css/memberpage.min.css');

        $members = $memberRepository->fetchAll([], [], 'ORDER BY level DESC, userName ASC');
        $this->addTemplateVars([
            'members' => $members,
            'pageLevels' => self::PAGE_LEVELS,
            'levels' => self::LEVELS,
        ]);
    }
}
