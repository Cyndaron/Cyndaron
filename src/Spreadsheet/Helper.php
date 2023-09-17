<?php
declare(strict_types=1);

namespace Cyndaron\Spreadsheet;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use function ob_get_clean;
use function Safe\ob_start;
use function str_replace;

final class Helper
{
    public static function convertToString(Spreadsheet $spreadsheet, string $type = IOFactory::WRITER_XLSX): string
    {
        ob_start();
        $writer = IOFactory::createWriter($spreadsheet, $type);
        $writer->save('php://output');
        return ob_get_clean() ?: '';
    }

    public static function getResponseHeadersForFilename(string $filename): array
    {
        $filename = str_replace('"', "'", $filename);
        return [
            'content-type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=UTF-8',
            'content-disposition' => 'attachment;filename="' . $filename . '"',
            'cache-control' => 'max-age=0'
        ];
    }
}
