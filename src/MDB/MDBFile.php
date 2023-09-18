<?php
declare(strict_types=1);

namespace Cyndaron\MDB;

use RuntimeException;
use function array_combine;
use function escapeshellarg;
use function fgetcsv;
use function file_exists;
use function fopen;
use function shell_exec;
use function tempnam;
use function unlink;
use function var_dump;
use function explode;
use function sprintf;
use function fclose;

final class MDBFile
{
    private const DATETIME_FORMAT = '%Y-%m-%d %H:%M:%S';
    private const DATE_FORMAT = '%Y-%m-%d';
    private const LIST_TABLES_COMMAND = 'mdb-tables -1 %s';
    private const EXPORT_TABLES_COMMAND = 'mdb-export -T %s -D %s %s %s > %s';

    public function __construct(private readonly string $path)
    {
    }

    /**
     * @return string[]
     */
    public function listTables(): array
    {
        $command = sprintf(self::LIST_TABLES_COMMAND, escapeshellarg($this->path));
        $output = shell_exec($command);
        if (empty($output))
        {
            throw new RuntimeException('No tables found!');
        }

        $tables = explode("\n", $output);
        return $tables;
    }

    /**
     * @param string $table
     * @return array<array<string, string>>
     */
    public function getTableData(string $table): array
    {
        $tempname = tempnam('/tmp', 'mdb');
        if ($tempname === false)
        {
            throw new RuntimeException('Could not create temporary file!');
        }

        $command = sprintf(
            self::EXPORT_TABLES_COMMAND,
            escapeshellarg(self::DATETIME_FORMAT),
            escapeshellarg(self::DATE_FORMAT),
            escapeshellarg($this->path),
            escapeshellarg($table),
            escapeshellarg($tempname)
        );

        $output = shell_exec($command);
        if (!empty($output))
        {
            throw new RuntimeException('Error while extracting: ' . $output);
        }

        if (!file_exists($tempname))
        {
            throw new RuntimeException('Unknown error while extracting');
        }

        $lines = [];
        $handle = fopen($tempname, 'rb');
        if ($handle === false)
        {
            throw new RuntimeException('Could not open file handle!');
        }

        $headers = fgetcsv($handle);
        if ($headers === false)
        {
            throw new RuntimeException('Could not read table data headers!');
        }

        while ($row = fgetcsv($handle))
        {
            $lines[] = array_combine($headers, $row);
        }

        fclose($handle);
        unlink($tempname);

        return $lines;
    }
}
