<?php
namespace Cyndaron\Widget;

class NeutralMessage extends Widget
{
    public function __construct(string $text)
    {
        $this->code = sprintf('<div class="alert alert-info">%s</div><br>', $text);
    }
}