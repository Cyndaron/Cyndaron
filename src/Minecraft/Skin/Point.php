<?php
namespace Cyndaron\Minecraft\Skin;

use function sin;
use function cos;
use function count;
use function min;
use function max;
use function assert;

/**
 * Class Point
 */
final class Point
{
    private CoordsXYZ $_originCoord;
    private CoordsXYZ|null $_destCoord = null;
    private bool $_isProjected = false;
    private bool $_isPreProjected = false;

    private float $sinAlpha;
    private float $cosAlpha;
    private float $sinOmega;
    private float $cosOmega;

    /**
     * Point constructor.
     * @param CoordsXYZ|null $originCoord
     * @param float $alpha
     * @param float $omega
     */
    public function __construct(CoordsXYZ|null $originCoord, float $alpha, float $omega)
    {
        $this->sinAlpha = sin($alpha);
        $this->cosAlpha = cos($alpha);
        $this->sinOmega = sin($omega);
        $this->cosOmega = cos($omega);

        if ($originCoord !== null)
        {
            $this->_originCoord = $originCoord;
        }
        else
        {
            $this->_originCoord = new CoordsXYZ(0, 0, 0);
        }
    }

    public function project(): void
    {
        // 1, 0, 1, 0
        $x = $this->_originCoord->x;
        $y = $this->_originCoord->y;
        $z = $this->_originCoord->z;
        $this->_destCoord = new CoordsXYZ(
            x: $x * $this->cosOmega + $z * $this->sinOmega,
            y: $x * $this->sinAlpha * $this->sinOmega + $y * $this->cosAlpha - $z * $this->sinAlpha * $this->cosOmega,
            z: -$x * $this->cosAlpha * $this->sinOmega + $y * $this->sinAlpha + $z * $this->cosAlpha * $this->cosOmega,
        );
        $this->_isProjected = true;
        SkinRenderer::$minX = (int)min(SkinRenderer::$minX, $this->_destCoord->x);
        SkinRenderer::$maxX = (int)max(SkinRenderer::$maxX, $this->_destCoord->x);
        SkinRenderer::$minY = (int)min(SkinRenderer::$minY, $this->_destCoord->y);
        SkinRenderer::$maxY = (int)max(SkinRenderer::$maxY, $this->_destCoord->y);
    }

    public function preProject(CoordsXYZ $delta, float $cosAlpha, float $sinAlpha, float $cosOmega, float $sinOmega): void
    {
        if (!$this->_isPreProjected)
        {
            $x = $this->_originCoord->x - $delta->x;
            $y = $this->_originCoord->y - $delta->y;
            $z = $this->_originCoord->z - $delta->z;
            $this->_originCoord = new CoordsXYZ(
                x: $x * $cosOmega + $z * $sinOmega + $delta->x,
                y: $x * $sinAlpha * $sinOmega + $y * $cosAlpha - $z * $sinAlpha * $cosOmega + $delta->y,
                z: -$x * $cosAlpha * $sinOmega + $y * $sinAlpha + $z * $cosAlpha * $cosOmega + $delta->z,
            );

            $this->_isPreProjected = true;
        }
    }

    /**
     * @return CoordsXYZ
     */
    public function getOriginCoord(): CoordsXYZ
    {
        return $this->_originCoord;
    }

    /**
     * @return CoordsXYZ|null
     */
    public function getDestCoord(): CoordsXYZ|null
    {
        return $this->_destCoord;
    }

    /**
     * @return float
     */
    public function getDepth(): float
    {
        if (!$this->_isProjected)
        {
            $this->project();
        }
        assert($this->_destCoord !== null);
        return $this->_destCoord->z;
    }

    /**
     * @return bool
     */
    public function isProjected(): bool
    {
        return $this->_isProjected;
    }
}
