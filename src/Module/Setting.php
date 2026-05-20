<?php
declare(strict_types=1);

namespace Cyndaron\Module;

final class Setting
{
    public function __construct(
        public readonly string $code,
        public readonly SettingType $type,
        public readonly string $description
    ) {
    }
}
