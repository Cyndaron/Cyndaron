<?php
namespace Cyndaron\Registration;

use Cyndaron\Template\Template;

class Util extends \Cyndaron\Util
{
    public static function drawPageManagerTab(): string
    {
        $templateVars = ['events' => Event::fetchAll()];
        return (new Template())->render('Registration/PageManagerTab', $templateVars);
    }

    public static function birthYearToCategory(int $birthYear): string
    {
        $age = date('Y') - $birthYear;

        if ($age < 12)
            return 'Niet opgegeven';

        static $ageRanges = [
            [12, 25], [26, 50], [51, 65], [66, 70], [71, 75], [76, 80]
        ];
        foreach ($ageRanges as $ageRange)
        {
            if ($age >= $ageRange[0] && $age <= $ageRange[1])
                return "$ageRange[0] - $ageRange[1]";
        }

        return '81+';
    }
}