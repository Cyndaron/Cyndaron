<?php
namespace Cyndaron\Minecraft\Skin;

final class CubePoint
{
    /**
     * @param Point $point
     * @param string[] $places
     */
    public function __construct(private readonly Point $point, private readonly array $places)
    {
    }

    public function getPoint(): Point
    {
        return $this->point;
    }

    /**
     * @return string[]
     */
    public function getPlaces(): array
    {
        return $this->places;
    }
}
