<?php
/**
 * Copyright © 2009-2024 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\Downloads;

use Cyndaron\Page\Page;
use function array_shift;

class DownloadPage extends Page
{
    /**
     * @param string $title
     * @param Build[] $builds
     */
    public function __construct(string $title, array $builds)
    {
        parent::__construct($title);
        $newestBuild = array_shift($builds);
        $this->addTemplateVar('newestBuild', $newestBuild);
        $this->addTemplateVar('olderBuilds', $builds);
    }
}
