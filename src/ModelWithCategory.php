<?php
namespace Cyndaron;

use Cyndaron\Template\ViewHelpers;

abstract class ModelWithCategory extends Model
{
    public string $name = '';
    public ?int $categoryId = null;
    public bool $showBreadcrumbs = false;

    abstract public function getFriendlyUrl(): string;

    public function getBlurb(): string
    {
        return html_entity_decode(ViewHelpers::wordlimit(trim($this->getText()), 30));
    }

    abstract public function getText(): string;

    public function getImage(): string
    {
        preg_match('/<img.*?src="(.*?)".*?>/si', $this->getText(), $match);
        return $match[1] ?? '';
    }
}
