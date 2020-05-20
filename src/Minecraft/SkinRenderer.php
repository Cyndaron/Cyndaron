<?php
namespace Cyndaron\Minecraft;

abstract class SkinRenderer
{
    public $skinSource;

    public function __construct(Skin $skin)
    {
        $this->skinSource = $skin->getSkinOrFallback();
    }

    abstract public function output(): string;
}