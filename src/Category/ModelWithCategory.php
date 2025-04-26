<?php
declare(strict_types=1);

namespace Cyndaron\Category;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;
use Cyndaron\View\Template\ViewHelpers;
use Safe\Exceptions\PcreException;
use function Safe\preg_match;
use function html_entity_decode;
use function trim;

/**
 * Class ModelWithCategory
 *
 * @property string $type May be present as a helper for the category settings
 */
abstract class ModelWithCategory extends Model
{
    #[DatabaseField]
    public string $name = '';
    #[DatabaseField]
    public string $image = '';
    #[DatabaseField]
    public string $previewImage = '';
    #[DatabaseField]
    public string $blurb = '';
    #[DatabaseField]
    public bool $showBreadcrumbs = false;

    // Saved in coupling table!
    public int $priority = 0;

    public const CATEGORY_TABLE = '';

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
        return $this->previewImage ?: $this->getImage() ?: $this->getImageFromText();
    }

    /**
     * Fetches the first image from the page body.
     * Most useful as a fallback.
     *
     * @return string
     */
    public function getImageFromText(): string
    {
        try
        {
            preg_match('/<img.*?src="(.*?)".*?>/si', $this->getText(), $match);
            return $match[1] ?? '';
        }
        catch (PcreException)
        {
            return '';
        }
    }

    public function shouldOpenInNewTab(): bool
    {
        return false;
    }
}
