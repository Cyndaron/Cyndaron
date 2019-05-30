<?php
namespace Cyndaron\Widget;

class PositiveMessage extends Widget
{
    public function __construct(string $text)
    {
        $this->code = sprintf('<div class="alert alert-success">%s</div><br>', $text);
    }
}