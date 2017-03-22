<?php
namespace Cyndaron\Widget;

class GoedeMelding extends Widget
{
    public function __construct(string $tekst)
    {
        $this->code = sprintf('<div class="alert alert-success">%s</div><br>', $tekst);
    }
}