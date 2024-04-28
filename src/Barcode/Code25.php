<?php
declare(strict_types=1);

namespace Cyndaron\Barcode;

use function count;
use function explode;
use function strlen;
use function substr;

/**
 * @author David S. Tufts, Michael Steenbeek
 * @company davidscotttufts.com
 * @license https://github.com/davidscotttufts/php-barcode/blob/master/LICENSE
 */
final class Code25 extends Barcode
{
    private const CODE_MAP_1 = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
    private const CODE_MAP_2 = ['3-1-1-1-3', '1-3-1-1-3', '3-3-1-1-1', '1-1-3-1-3', '3-1-3-1-1', '1-3-3-1-1', '1-1-1-3-3', '3-1-1-3-1', '1-3-1-3-1', '1-1-3-3-1'];

    protected function getCodeString(): string
    {
        $codeString = '';

        $strlen = strlen($this->text);
        $codeArray1Count = count(self::CODE_MAP_1);
        $temp = [];
        for ($x = 1; $x <= $strlen; $x++)
        {
            for ($y = 0; $y < $codeArray1Count; $y++)
            {
                if (substr($this->text, ($x - 1), 1) === self::CODE_MAP_1[$y])
                {
                    $temp[$x] = self::CODE_MAP_2[$y];
                }
            }
        }

        $strlen = strlen($this->text);
        for ($x = 1; $x <= $strlen; $x += 2)
        {
            if (isset($temp[$x]) && isset($temp[($x + 1)]))
            {
                $temp1 = explode("-", $temp[$x]);
                $temp2 = explode("-", $temp[($x + 1)]);
                $temp1Count = count($temp1);
                for ($y = 0; $y < $temp1Count; $y++)
                {
                    $codeString .= $temp1[$y] . $temp2[$y];
                }
            }
        }

        $codeString = "1111{$codeString}311";

        return $codeString;
    }
}
