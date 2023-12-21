<?php
declare(strict_types=1);

namespace Cyndaron\Imaging;

use Cyndaron\Util\Filetypes;
use Cyndaron\Util\Util;
use RuntimeException;
use function explode;
use function is_string;
use function md5;
use function preg_replace_callback;
use function Safe\base64_decode;
use function Safe\date;
use function Safe\file_put_contents;
use function str_replace;

final class ImageExtractor
{
    public function __construct(private readonly string $outputDir)
    {
    }

    /**
     * Extracts any inline image in the given source text, and returns the modified text.
     *
     * @param string $source
     * @throws RuntimeException If extraction fails
     * @return string
     */
    public function process(string $source): string
    {
        $result = preg_replace_callback('/src="(data:)([^"]*)"/', $this->extractImages(...), $source);
        if (!is_string($result))
        {
            throw new RuntimeException('Error while parsing text for inline images!');
        }
        return $result;
    }

    /**
     * @param array<string> $matches
     * @throws \Safe\Exceptions\DatetimeException
     * @throws \Safe\Exceptions\FilesystemException
     * @throws \Safe\Exceptions\UrlException
     * @return string
     */
    private function extractImages(array $matches): string
    {
        [$type, $image] = explode(';', $matches[2]);

        $extension = Filetypes::MIME_TYPE_TO_EXTENSION[$type] ?? null;
        if ($extension === null)
        {
            return 'src="' . $matches[0] . '"';
        }

        $image = str_replace('base64,', '', $image);
        $image = base64_decode(str_replace(' ', '+', $image), true);
        $destinationFilename = $this->outputDir . '/' . date('c') . '-' . md5($image) . '.' . $extension;
        Util::createDir($this->outputDir);
        file_put_contents($destinationFilename, $image);

        return 'src="' . Util::filenameToUrl($destinationFilename) . '"';
    }
}
