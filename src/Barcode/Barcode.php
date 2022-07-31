<?php
declare(strict_types=1);

namespace Cyndaron\Barcode;

use function header;
use function imagepng;
use function ob_end_clean;
use function Safe\imagedestroy;
use function strtolower;

/**
 * @author David S. Tufts, Michael Steenbeek
 * @company davidscotttufts.com
 * @license https://github.com/davidscotttufts/php-barcode/blob/master/LICENSE
 */
final class Barcode
{
    private $image;

    public function __construct($text, int $size = 20, $orientation = "horizontal", string $codeType = BarcodeType::CODE_128, $print = false, float $sizeFactor = 1.0)
    {
        $code_string = "";
        // Translate the $text into barcode the correct $code_type
        if (in_array($codeType, [BarcodeType::CODE_128, BarcodeType::CODE_128B], true))
        {
            $chksum = 104;
            // Must not change order of array elements as the checksum depends on the array's key to validate final code
            $code_array = [" " => "212222", "!" => "222122", "\"" => "222221", "#" => "121223", "$" => "121322", "%" => "131222", "&" => "122213", "'" => "122312", "(" => "132212", ")" => "221213", "*" => "221312", "+" => "231212", "," => "112232", "-" => "122132", "." => "122231", "/" => "113222", "0" => "123122", "1" => "123221", "2" => "223211", "3" => "221132", "4" => "221231", "5" => "213212", "6" => "223112", "7" => "312131", "8" => "311222", "9" => "321122", ":" => "321221", ";" => "312212", "<" => "322112", "=" => "322211", ">" => "212123", "?" => "212321", "@" => "232121", "A" => "111323", "B" => "131123", "C" => "131321", "D" => "112313", "E" => "132113", "F" => "132311", "G" => "211313", "H" => "231113", "I" => "231311", "J" => "112133", "K" => "112331", "L" => "132131", "M" => "113123", "N" => "113321", "O" => "133121", "P" => "313121", "Q" => "211331", "R" => "231131", "S" => "213113", "T" => "213311", "U" => "213131", "V" => "311123", "W" => "311321", "X" => "331121", "Y" => "312113", "Z" => "312311", "[" => "332111", "\\" => "314111", "]" => "221411", "^" => "431111", "_" => "111224", "\`" => "111422", "a" => "121124", "b" => "121421", "c" => "141122", "d" => "141221", "e" => "112214", "f" => "112412", "g" => "122114", "h" => "122411", "i" => "142112", "j" => "142211", "k" => "241211", "l" => "221114", "m" => "413111", "n" => "241112", "o" => "134111", "p" => "111242", "q" => "121142", "r" => "121241", "s" => "114212", "t" => "124112", "u" => "124211", "v" => "411212", "w" => "421112", "x" => "421211", "y" => "212141", "z" => "214121", "{" => "412121", "|" => "111143", "}" => "111341", "~" => "131141", "DEL" => "114113", "FNC 3" => "114311", "FNC 2" => "411113", "SHIFT" => "411311", "CODE C" => "113141", "FNC 4" => "114131", "CODE A" => "311141", "FNC 1" => "411131", "Start A" => "211412", "Start B" => "211214", "Start C" => "211232", "Stop" => "2331112"];
            $code_keys = array_keys($code_array);
            $code_values = array_flip($code_keys);
            for ($X = 1; $X <= strlen($text); $X++)
            {
                $activeKey = substr($text, ($X - 1), 1);
                $code_string .= $code_array[$activeKey];
                $chksum = ($chksum + ($code_values[$activeKey] * $X));
            }
            $code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];

            $code_string = "211214" . $code_string . "2331112";
        }
        elseif ($codeType == BarcodeType::CODE_128A)
        {
            $chksum = 103;
            // Code 128A doesn't support lower case
            $text = strtoupper($text);
            // Must not change order of array elements as the checksum depends on the array's key to validate final code
            $code_array = [" " => "212222", "!" => "222122", "\"" => "222221", "#" => "121223", "$" => "121322", "%" => "131222", "&" => "122213", "'" => "122312", "(" => "132212", ")" => "221213", "*" => "221312", "+" => "231212", "," => "112232", "-" => "122132", "." => "122231", "/" => "113222", "0" => "123122", "1" => "123221", "2" => "223211", "3" => "221132", "4" => "221231", "5" => "213212", "6" => "223112", "7" => "312131", "8" => "311222", "9" => "321122", ":" => "321221", ";" => "312212", "<" => "322112", "=" => "322211", ">" => "212123", "?" => "212321", "@" => "232121", "A" => "111323", "B" => "131123", "C" => "131321", "D" => "112313", "E" => "132113", "F" => "132311", "G" => "211313", "H" => "231113", "I" => "231311", "J" => "112133", "K" => "112331", "L" => "132131", "M" => "113123", "N" => "113321", "O" => "133121", "P" => "313121", "Q" => "211331", "R" => "231131", "S" => "213113", "T" => "213311", "U" => "213131", "V" => "311123", "W" => "311321", "X" => "331121", "Y" => "312113", "Z" => "312311", "[" => "332111", "\\" => "314111", "]" => "221411", "^" => "431111", "_" => "111224", "NUL" => "111422", "SOH" => "121124", "STX" => "121421", "ETX" => "141122", "EOT" => "141221", "ENQ" => "112214", "ACK" => "112412", "BEL" => "122114", "BS" => "122411", "HT" => "142112", "LF" => "142211", "VT" => "241211", "FF" => "221114", "CR" => "413111", "SO" => "241112", "SI" => "134111", "DLE" => "111242", "DC1" => "121142", "DC2" => "121241", "DC3" => "114212", "DC4" => "124112", "NAK" => "124211", "SYN" => "411212", "ETB" => "421112", "CAN" => "421211", "EM" => "212141", "SUB" => "214121", "ESC" => "412121", "FS" => "111143", "GS" => "111341", "RS" => "131141", "US" => "114113", "FNC 3" => "114311", "FNC 2" => "411113", "SHIFT" => "411311", "CODE C" => "113141", "CODE B" => "114131", "FNC 4" => "311141", "FNC 1" => "411131", "Start A" => "211412", "Start B" => "211214", "Start C" => "211232", "Stop" => "2331112"];
            $code_keys = array_keys($code_array);
            $code_values = array_flip($code_keys);
            for ($X = 1; $X <= strlen($text); $X++)
            {
                $activeKey = substr($text, ($X - 1), 1);
                $code_string .= $code_array[$activeKey];
                $chksum = ($chksum + ($code_values[$activeKey] * $X));
            }
            $code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];

            $code_string = "211412" . $code_string . "2331112";
        }
        elseif ($codeType === BarcodeType::CODE_39)
        {
            $code_array = ["0" => "111221211", "1" => "211211112", "2" => "112211112", "3" => "212211111", "4" => "111221112", "5" => "211221111", "6" => "112221111", "7" => "111211212", "8" => "211211211", "9" => "112211211", "A" => "211112112", "B" => "112112112", "C" => "212112111", "D" => "111122112", "E" => "211122111", "F" => "112122111", "G" => "111112212", "H" => "211112211", "I" => "112112211", "J" => "111122211", "K" => "211111122", "L" => "112111122", "M" => "212111121", "N" => "111121122", "O" => "211121121", "P" => "112121121", "Q" => "111111222", "R" => "211111221", "S" => "112111221", "T" => "111121221", "U" => "221111112", "V" => "122111112", "W" => "222111111", "X" => "121121112", "Y" => "221121111", "Z" => "122121111", "-" => "121111212", "." => "221111211", " " => "122111211", "$" => "121212111", "/" => "121211121", "+" => "121112121", "%" => "111212121", "*" => "121121211"];

            // Convert to uppercase
            $upper_text = strtoupper($text);

            for ($X = 1; $X <= strlen($upper_text); $X++)
            {
                $code_string .= $code_array[substr($upper_text, ($X - 1), 1)] . "1";
            }

            $code_string = "1211212111" . $code_string . "121121211";
        }
        elseif ($codeType === BarcodeType::CODE_25)
        {
            $code_array1 = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0"];
            $code_array2 = ["3-1-1-1-3", "1-3-1-1-3", "3-3-1-1-1", "1-1-3-1-3", "3-1-3-1-1", "1-3-3-1-1", "1-1-1-3-3", "3-1-1-3-1", "1-3-1-3-1", "1-1-3-3-1"];

            for ($X = 1; $X <= strlen($text); $X++)
            {
                for ($Y = 0; $Y < count($code_array1); $Y++)
                {
                    if (substr($text, ($X - 1), 1) == $code_array1[$Y])
                    {
                        $temp[$X] = $code_array2[$Y];
                    }
                }
            }

            for ($X = 1; $X <= strlen($text); $X += 2)
            {
                if (isset($temp[$X]) && isset($temp[($X + 1)]))
                {
                    $temp1 = explode("-", $temp[$X]);
                    $temp2 = explode("-", $temp[($X + 1)]);
                    for ($Y = 0; $Y < count($temp1); $Y++)
                    {
                        $code_string .= $temp1[$Y] . $temp2[$Y];
                    }
                }
            }

            $code_string = "1111" . $code_string . "311";
        }
        elseif ($codeType === BarcodeType::CODABAR)
        {
            $code_array1 = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0", "-", "$", ":", "/", ".", "+", "A", "B", "C", "D"];
            $code_array2 = ["1111221", "1112112", "2211111", "1121121", "2111121", "1211112", "1211211", "1221111", "2112111", "1111122", "1112211", "1122111", "2111212", "2121112", "2121211", "1121212", "1122121", "1212112", "1112122", "1112221"];

            // Convert to uppercase
            $upper_text = strtoupper($text);

            for ($X = 1; $X <= strlen($upper_text); $X++)
            {
                for ($Y = 0; $Y < count($code_array1); $Y++)
                {
                    if (substr($upper_text, ($X - 1), 1) == $code_array1[$Y])
                    {
                        $code_string .= $code_array2[$Y] . "1";
                    }
                }
            }
            $code_string = "11221211" . $code_string . "1122121";
        }

        // Pad the edges of the barcode
        $code_length = 20;
        if ($print)
        {
            $text_height = 30;
        }
        else
        {
            $text_height = 0;
        }

        for ($i = 1; $i <= strlen($code_string); $i++)
        {
            $code_length = $code_length + (integer)(substr($code_string, ($i - 1), 1));
        }

        if (strtolower($orientation) === "horizontal")
        {
            $img_width = (int)ceil($code_length * $sizeFactor);
            $img_height = $size;
        }
        else
        {
            $img_width = $size;
            $img_height = (int)ceil($code_length * $sizeFactor);
        }

        $this->image = imagecreate($img_width, $img_height + $text_height);
        $black = imagecolorallocate($this->image, 0, 0, 0);
        $white = imagecolorallocate($this->image, 255, 255, 255);

        imagefill($this->image, 0, 0, $white);
        if ($print)
        {
            imagestring($this->image, 5, 31, $img_height, $text, $black);
        }

        $location = 10;
        for ($position = 1; $position <= strlen($code_string); $position++)
        {
            $cur_size = $location + (substr($code_string, ($position - 1), 1));
            if (strtolower($orientation) === "horizontal")
            {
                imagefilledrectangle($this->image, (int)ceil($location * $sizeFactor), 0, (int)ceil($cur_size * $sizeFactor), $img_height, ($position % 2 == 0 ? $white : $black));
            }
            else
            {
                imagefilledrectangle($this->image, 0, (int)ceil($location * $sizeFactor), $img_width, (int)ceil($cur_size * $sizeFactor), ($position % 2 == 0 ? $white : $black));
            }
            $location = $cur_size;
        }
    }

    public function __destruct()
    {
        imagedestroy($this->image);
    }

    public function getOutput(): string
    {
        ob_start();
        imagepng($this->image);
        return ob_get_clean();
    }

    public function toFile(string $filepath)
    {
        imagepng($this->image, $filepath);
    }

}
