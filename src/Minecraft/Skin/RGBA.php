<?php
namespace Cyndaron\Minecraft\Skin;

class RGBA
{
    public int $red;
    public int $green;
    public int $blue;
    public int $alpha;

    public function __construct(int $colour)
    {
        $this->red = ($colour >> 16) & 0xFF;
        $this->green = ($colour >> 8) & 0xFF;
        $this->blue = $colour & 0xFF;
        $this->alpha = ($colour >> 24) & 0xFF;
    }
}
