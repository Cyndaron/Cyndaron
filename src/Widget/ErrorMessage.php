<?php
namespace Cyndaron\Widget;

class ErrorMessage extends Widget
{
    public function __construct(string $text)
    {
        $this->code = sprintf('<div class="alert alert-warning">%s</div><br>', $text);
    }
}