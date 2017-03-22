<?php
namespace Cyndaron\Widget;

class NeutraleMelding extends Widget
{
    public function __construct(string $tekst)
    {
        $this->code = sprintf('<div class="alert alert-info">%s</div><br>', $tekst);
    }
}