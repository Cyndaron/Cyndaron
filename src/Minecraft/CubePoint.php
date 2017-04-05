<?php
namespace Cyndaron\Minecraft;

class CubePoint
{
    /** @var Point $point */
    private $point;
    /** @var array $places */
    private $places;

    public function __construct(Point $point, array $places)
    {
        $this->point = $point;
        $this->places = $places;
    }

    public function getPoint(): Point
    {
        return $this->point;
    }

    public function getPlaces(): array
    {
        return $this->places;
    }
}