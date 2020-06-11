<?php
namespace Cyndaron;

use Cyndaron\Template\ViewHelpers;

abstract class ModelWithCategory extends Model
{
    public string $name = '';
    public string $image = '';
    public string $blurb = '';
    public ?int $categoryId = null;
    public bool $showBreadcrumbs = false;

    abstract public function getFriendlyUrl(): string;

    public function getBlurb(): string
    {
        $text = $this->blurb ?: $this->getText();
        return html_entity_decode(ViewHelpers::wordlimit(trim($text), 30));
    }

    abstract public function getText(): string;

    public function getImage(): string
    {
        if ($this->image)
        {
            return $this->image;
        }

        preg_match('/<img.*?src="(.*?)".*?>/si', $this->getText(), $match);
        return $match[1] ?? '';
    }
}
