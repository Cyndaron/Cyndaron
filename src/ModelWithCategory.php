<?php
namespace Cyndaron;

class ModelWithCategory extends Model
{
    public string $name = '';
    public ?int $categoryId = null;
    public bool $showBreadcrumbs = false;
}