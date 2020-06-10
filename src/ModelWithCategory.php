<?php
namespace Cyndaron;

abstract class ModelWithCategory extends Model
{
    public string $name = '';
    public ?int $categoryId = null;
    public bool $showBreadcrumbs = false;

    abstract public function getFriendlyUrl(): string;

    abstract public function getBlurb(): string;

    abstract public function getImage(): string;
}
