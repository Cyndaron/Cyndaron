<?php
declare(strict_types=1);

namespace Cyndaron\Module;

interface Templated
{
    public function getTemplateRoot(): TemplateRoot;
}
