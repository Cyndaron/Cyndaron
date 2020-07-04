<?php
namespace Cyndaron\Minecraft\Skin;

/**
 * Class Point
 */
final class Point
{
    private array $_originCoord;
    private array $_destCoord = [];
    private bool $_isProjected = false;
    private bool $_isPreProjected = false;

    private float $sinAlpha;
    private float $cosAlpha;
    private float $sinOmega;
    private float $cosOmega;

    /**
     * Point constructor.
     * @param array $originCoord
     * @param float $alpha
     * @param float $omega
     */
    public function __construct(array $originCoord, float $alpha, float $omega)
    {
        $this->sinAlpha = sin($alpha);
        $this->cosAlpha = cos($alpha);
        $this->sinOmega = sin($omega);
        $this->cosOmega = cos($omega);

        if (count($originCoord) === 3)
        {
            $this->_originCoord = [
                'x' => ($originCoord['x'] ?? 0),
                'y' => ($originCoord['y'] ?? 0),
                'z' => ($originCoord['z'] ?? 0),
            ];
        }
        else
        {
            $this->_originCoord = ['x' => 0, 'y' => 0, 'z' => 0];
        }
    }

    public function project(): void
    {
        // 1, 0, 1, 0
        $x = $this->_originCoord['x'];
        $y = $this->_originCoord['y'];
        $z = $this->_originCoord['z'];
        $this->_destCoord['x'] = $x * $this->cosOmega + $z * $this->sinOmega;
        $this->_destCoord['y'] = $x * $this->sinAlpha * $this->sinOmega + $y * $this->cosAlpha - $z * $this->sinAlpha * $this->cosOmega;
        $this->_destCoord['z'] = -$x * $this->cosAlpha * $this->sinOmega + $y * $this->sinAlpha + $z * $this->cosAlpha * $this->cosOmega;
        $this->_isProjected = true;
        SkinRenderer::$minX = min(SkinRenderer::$minX, $this->_destCoord['x']);
        SkinRenderer::$maxX = max(SkinRenderer::$maxX, $this->_destCoord['x']);
        SkinRenderer::$minY = min(SkinRenderer::$minY, $this->_destCoord['y']);
        SkinRenderer::$maxY = max(SkinRenderer::$maxY, $this->_destCoord['y']);
    }

    public function preProject(int $dx, int $dy, int $dz, float $cosAlpha, float $sinAlpha, float $cosOmega, float $sinOmega): void
    {
        if (!$this->_isPreProjected)
        {
            $x = $this->_originCoord['x'] - $dx;
            $y = $this->_originCoord['y'] - $dy;
            $z = $this->_originCoord['z'] - $dz;
            $this->_originCoord['x'] = $x * $cosOmega + $z * $sinOmega + $dx;
            $this->_originCoord['y'] = $x * $sinAlpha * $sinOmega + $y * $cosAlpha - $z * $sinAlpha * $cosOmega + $dy;
            $this->_originCoord['z'] = -$x * $cosAlpha * $sinOmega + $y * $sinAlpha + $z * $cosAlpha * $cosOmega + $dz;
            $this->_isPreProjected = true;
        }
    }

    /**
     * @return array
     */
    public function getOriginCoord(): array
    {
        return $this->_originCoord;
    }

    /**
     * @return array
     */
    public function getDestCoord(): array
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
        return $this->_destCoord['z'];
    }

    /**
     * @return bool
     */
    public function isProjected(): bool
    {
        return $this->_isProjected;
    }
}
