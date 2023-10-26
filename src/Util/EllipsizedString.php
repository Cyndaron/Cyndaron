<?php
declare(strict_types=1);

namespace Cyndaron\Util;

use Stringable;

use function strrpos;
use function strpos;
use function strlen;
use function substr;

final class EllipsizedString implements Stringable
{
    private readonly string $string;

    public function __construct(string $string, int $desiredLength)
    {
        if (strlen($string) > $desiredLength)
        {
            $string = $this->process($string, $desiredLength);
        }

        $this->string = $string;
    }

    private function process(string $string, int $desiredLength): string
    {
        $space1Pos = strrpos(substr($string, 0, $desiredLength), ' ');
        $space2Pos = strpos($string, ' ', $desiredLength) ?: strlen($string);

        $noSpaceNearby = ($space1Pos === false && ($space2Pos + 10) > strlen($string)) ||
            ($space2Pos > $desiredLength + 10);

        if ($noSpaceNearby)
        {
            return substr($string, 0, $desiredLength) . '…';
        }

        $space1PosIsCloserToDesiredLength = $space1Pos !== false &&
            ($desiredLength - $space1Pos < $space2Pos - $desiredLength);
        if ($space1PosIsCloserToDesiredLength)
        {
            return substr($string, 0, $space1Pos) . '…';
        }

        if ($space2Pos !== strlen($string))
        {
            return substr($string, 0, $space2Pos) . '…';
        }

        return substr($string, 0, $space2Pos);
    }

    public function __toString(): string
    {
        return $this->string;
    }
}
