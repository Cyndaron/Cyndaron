<?php
namespace Cyndaron\Registration;

use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\View\Template\TemplateRenderer;
use function constant;
use function defined;
use function Safe\date;
use const INF;

final class Util
{
    public const AGE_RANGES_VOV_1 = [
        [12, 25], [26, 50], [51, 65], [66, 70], [71, 75], [76, 80], [81, INF]
    ];
    public const AGE_RANGES_VOV_2 = [
        [0, 40], [41, 50], [51, 60], [61, 70], [71, 80], [81, INF]
    ];

    public static function drawPageManagerTab(TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler, EventRepository $eventRepository): string
    {
        $templateVars = [
            'events' => $eventRepository->fetchAll(),
            'tokenDelete' => $tokenHandler->get('event', 'delete'),
        ];
        return $templateRenderer->render('Registration/PageManagerTab', $templateVars);
    }

    public static function birthYearToCategory(Event $event, int|null $birthYear): string
    {
        if ($birthYear === null)
        {
            return 'Niet opgegeven';
        }

        $age = (int)date('Y') - $birthYear;

        foreach (self::getAgeRanges($event) as $ageRange)
        {
            if ($age >= $ageRange[0] && $age <= $ageRange[1])
            {
                if ($ageRange[0] === 0)
                {
                    return "t/m $ageRange[1]";
                }
                if ($ageRange[1] === INF)
                {
                    return "$ageRange[0]+";
                }

                return "$ageRange[0] - $ageRange[1]";
            }
        }

        return 'Niet opgegeven';
    }

    /**
     * @param Event $event
     * @return list<array{0: int|float, 1: int|float}>
     */
    public static function getAgeRanges(Event $event): array
    {
        $constName = "\Cyndaron\Registration\Util::AGE_RANGES_VOV_{$event->id}";
        if (!defined($constName))
        {
            $constName = "\Cyndaron\Registration\Util::AGE_RANGES_VOV_1";
        }

        /** @var list<array{0: int|float, 1: int|float}> $ret */
        $ret = constant($constName);
        return $ret;
    }
}
