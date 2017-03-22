<?php
namespace Cyndaron\Widget;

class Foutmelding extends Widget
{
    public function __construct(string $tekst)
    {
        $this->code = sprintf('<div class="alert alert-warning">%s</div><br>', $tekst);
    }
}