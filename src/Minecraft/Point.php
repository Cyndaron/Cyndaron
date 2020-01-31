<?php
namespace Cyndaron\Minecraft;

/**
 * Class Point
 */
class Point
{
    private array $_originCoord;
    private array $_destCoord = [];
    private bool $_isProjected = false;
    private bool $_isPreProjected = false;

    /**
     * Point constructor.
     * @param $originCoord
     */
    public function __construct(array $originCoord)
    {
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
        $cos_alpha = SkinRendererHandler::$cos_alpha;
        $sin_alpha = SkinRendererHandler::$sin_alpha;
        $cos_omega = SkinRendererHandler::$cos_omega;
        $sin_omega = SkinRendererHandler::$sin_omega;

        // 1, 0, 1, 0
        $x = $this->_originCoord['x'];
        $y = $this->_originCoord['y'];
        $z = $this->_originCoord['z'];
        $this->_destCoord['x'] = $x * $cos_omega + $z * $sin_omega;
        $this->_destCoord['y'] = $x * $sin_alpha * $sin_omega + $y * $cos_alpha - $z * $sin_alpha * $cos_omega;
        $this->_destCoord['z'] = -$x * $cos_alpha * $sin_omega + $y * $sin_alpha + $z * $cos_alpha * $cos_omega;
        $this->_isProjected = true;
        SkinRendererHandler::$minX = min(SkinRendererHandler::$minX, $this->_destCoord['x']);
        SkinRendererHandler::$maxX = max(SkinRendererHandler::$maxX, $this->_destCoord['x']);
        SkinRendererHandler::$minY = min(SkinRendererHandler::$minY, $this->_destCoord['y']);
        SkinRendererHandler::$maxY = max(SkinRendererHandler::$maxY, $this->_destCoord['y']);
    }

    public function preProject($dx, $dy, $dz, $cos_alpha, $sin_alpha, $cos_omega, $sin_omega): void
    {
        if (!$this->_isPreProjected)
        {
            $x = $this->_originCoord['x'] - $dx;
            $y = $this->_originCoord['y'] - $dy;
            $z = $this->_originCoord['z'] - $dz;
            $this->_originCoord['x'] = $x * $cos_omega + $z * $sin_omega + $dx;
            $this->_originCoord['y'] = $x * $sin_alpha * $sin_omega + $y * $cos_alpha - $z * $sin_alpha * $cos_omega + $dy;
            $this->_originCoord['z'] = -$x * $cos_alpha * $sin_omega + $y * $sin_alpha + $z * $cos_alpha * $cos_omega + $dz;
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
     * @return array
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