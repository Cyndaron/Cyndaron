<?php
namespace Cyndaron;

use Cyndaron\Template\ViewHelpers;

abstract class ModelWithCategory extends Model
{
    public string $name = '';
    public string $image = '';
    public string $previewImage = '';
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
        return $this->image;
    }

    public function getPreviewImage(): string
    {
        return $this->previewImage ?: $this->getImage();
    }
}
