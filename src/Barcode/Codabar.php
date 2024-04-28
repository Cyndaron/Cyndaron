<?php
declare(strict_types=1);

namespace Cyndaron\Barcode;

use function count;
use function strlen;
use function strtoupper;
use function substr;

/**
 * @author David S. Tufts, Michael Steenbeek
 * @company davidscotttufts.com
 * @license https://github.com/davidscotttufts/php-barcode/blob/master/LICENSE
 */
final class Codabar extends Barcode
{
    private const CODE_MAP_1 = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '-', '$', ':', '/', '.', '+', 'A', 'B', 'C', 'D'];
    private const CODE_MAP_2 = ['1111221', '1112112', '2211111', '1121121', '2111121', '1211112', '1211211', '1221111', '2112111', '1111122', '1112211', '1122111', '2111212', '2121112', '2121211', '1121212', '1122121', '1212112', '1112122', '1112221'];

    protected function getCodeString(): string
    {
        $codeString = '';

        $text = strtoupper($this->text);

        $strlen = strlen($text);
        $codeArray1Count = count(self::CODE_MAP_1);
        for ($x = 1; $x <= $strlen; $x++)
        {
            for ($y = 0; $y < $codeArray1Count; $y++)
            {
                if (substr($text, ($x - 1), 1) === self::CODE_MAP_1[$y])
                {
                    $codeString .= self::CODE_MAP_2[$y] . "1";
                }
            }
        }
        $codeString = "11221211{$codeString}1122121";

        return $codeString;
    }
}
