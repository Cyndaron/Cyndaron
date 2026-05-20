<?php
declare(strict_types=1);

namespace Cyndaron\Module;

enum SettingType
{
    case COLOR;
    case CHECKBOX;
    case EMAIL;
    case FILENAME_WITH_DIRECTORY;
    case HTML;
    case INTEGER;
    case SIMPLE_STRING;
    case URL;
}
