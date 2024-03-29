<?php
namespace Cyndaron\Minecraft\Skin;

use GdImage;
use function imagefilledpolygon;
use function imagecolorallocate;
use function assert;

/**
 * Class Polygon
 */
final class Polygon
{
    /**
     * @var Point[]
     */
    private array $_dots;

    /**
     * @var RGBA
     */
    private RGBA $_colour;

    /**
     * @var bool
     */
    private bool $_isProjected = false;

    /**
     * @var string
     */
    private string $_face = 'w';

    /**
     * Polygon constructor.
     * @param Point[] $dots
     * @param RGBA $colour
     */
    public function __construct(array $dots, RGBA $colour)
    {
        $this->_dots = $dots;
        $this->_colour = $colour;
        $coord0 = $dots[0]->getOriginCoord();
        $coord1 = $dots[1]->getOriginCoord();
        $coord2 = $dots[2]->getOriginCoord();
        if ($coord0->x === $coord1->x && $coord1->x === $coord2->x)
        {
            $this->_face = 'x';
        }
        elseif ($coord0->y === $coord1->y && $coord1->y === $coord2->y)
        {
            $this->_face = 'y';
        }
        elseif ($coord0->z === $coord1->z && $coord1->z === $coord2->z)
        {
            $this->_face = 'z';
        }
    }

    /**
     * @return string
     */
    public function getFace(): string
    {
        return $this->_face;
    }

    /**
     * @param int $ratio
     * @return string
     */
    public function getSvgPolygon($ratio): string
    {
        $points_2d = '';
        $r = $this->_colour->red;
        $g = $this->_colour->green;
        $b = $this->_colour->blue;
        $vR = (127 - ($this->_colour->alpha & 0x7F)) / 127;
        if ($vR === 0)
        {
            return '';
        }
        foreach ($this->_dots as $dot)
        {
            $coord = $dot->getDestCoord();
            assert($coord !== null);
            $points_2d .= $coord->x * $ratio . ',' . $coord->y * $ratio . ' ';
        }
        $alpha = 1; // TODO: Implement this :)
        return '<polygon points="' . $points_2d . '" style="fill:rgba(' . $r . ',' . $g . ',' . $b . ',' . $alpha . ')" />' . "\n";
    }

    /**
     * @param GdImage $image
     * @param int $minX
     * @param int $minY
     * @param int $ratio
     * @throws \Safe\Exceptions\ImageException
     */
    public function addPngPolygon(&$image, $minX, $minY, $ratio): void
    {
        $points_2d = [];
        $nb_points = 0;
        $r = $this->_colour->red;
        $g = $this->_colour->green;
        $b = $this->_colour->blue;
        $vR = (127 - ($this->_colour->alpha & 0x7F)) / 127;
        if ($vR === 0)
        {
            return;
        }
        $same_plan_x = true;
        $same_plan_y = true;
        foreach ($this->_dots as $dot)
        {
            $coord = $dot->getDestCoord();
            assert($coord !== null);
            if (!isset($coord_x))
            {
                $coord_x = $coord->x;
            }
            if (!isset($coord_y))
            {
                $coord_y = $coord->y;
            }
            if ($coord_x !== $coord->x)
            {
                $same_plan_x = false;
            }
            if ($coord_y !== $coord->y)
            {
                $same_plan_y = false;
            }
            $points_2d[] = ($coord->x - $minX) * $ratio;
            $points_2d[] = ($coord->y - $minY) * $ratio;
            $nb_points++;
        }
        if (!$same_plan_x && !$same_plan_y)
        {
            $colour = imagecolorallocate($image, $r, $g, $b);
            assert($colour !== false);
            imagefilledpolygon($image, $points_2d, $nb_points, $colour);
        }
    }

    /**
     * @return bool
     */
    public function isProjected(): bool
    {
        return $this->_isProjected;
    }

    public function project(): void
    {
        foreach ($this->_dots as $dot)
        {
            if (!$dot->isProjected())
            {
                $dot->project();
            }
        }
        $this->_isProjected = true;
    }

    public function preProject(CoordsXYZ $delta, float $cosAlpha, float $sinAlpha, float $cosOmega, float $sinOmega): void
    {
        foreach ($this->_dots as &$dot)
        {
            $dot->preProject($delta, $cosAlpha, $sinAlpha, $cosOmega, $sinOmega);
        }
    }
}
