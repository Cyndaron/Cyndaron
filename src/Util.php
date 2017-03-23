<?php
namespace Cyndaron;

class Util
{
    public static function woordlimiet($string, $lengte = 50, $ellips = "...")
    {
        $string = strip_tags($string);
        $words = explode(' ', $string);
        if (count($words) > $lengte)
            return implode(' ', array_slice($words, 0, $lengte)) . $ellips;
        else
            return $string;
    }
}