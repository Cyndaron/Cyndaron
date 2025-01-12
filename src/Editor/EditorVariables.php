<?php
declare(strict_types=1);

namespace Cyndaron\Editor;

use Cyndaron\Util\Link;

final class EditorVariables
{
    public function __construct(
        public readonly int|null $id,
        public readonly bool $useBackup,
        /** @var Link[] $internalLinks */
        public readonly array $internalLinks,
    ) {
    }
}
