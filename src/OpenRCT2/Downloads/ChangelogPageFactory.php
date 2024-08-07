<?php
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\Downloads;

use Cyndaron\Page\Page;
use Cyndaron\Page\SimplePage;
use function explode;
use function str_starts_with;
use function substr;
use function preg_replace;
use function implode;
use function preg_replace_callback;
use function str_replace;
use function sprintf;

final class ChangelogPageFactory
{
    public function __construct(private readonly APIFetcher $fetcher)
    {
    }

    public function getPage(): Page
    {
        $page = new SimplePage('Changelog', $this->getContents());
        return $page->toPage();
    }

    private function getContents(): string
    {
        $contents = $this->fetcher->fetch(APICall::CHANGELOG);

        // Titles
        /** @var string $contents */
        $contents = preg_replace('/([0-9].*?)\n(----+\n)/', '<h2>$1</h2>' . "\n", $contents);

        $lines = explode(PHP_EOL, $contents);
        $inUl = false;
        foreach ($lines as &$line)
        {
            if (str_starts_with($line, '- '))
            {
                $line = '<li>' . substr($line, 2) . '</li>';
                /** @var string $line */
                $line = preg_replace('/([^A-Za-z])#([0-9]+)/', '$1<a href="https://github.com/OpenRCT2/OpenRCT2/issues/$2">#$2</a>', $line);
                $line = preg_replace_callback('/([A-Za-z]+)#([0-9]+)/', static function(array $matches)
                {
                    $repo = str_replace('OpenSFX', 'OpenSoundEffects', $matches[1]);
                    $index = (int)$matches[2];
                    return sprintf('<a href="https://github.com/OpenRCT2/%s/issues/%d">%s#%d</a>', $repo, $index, $matches[1], $index);
                }, $line);
                if (!$inUl)
                {
                    $line = '<ul>' . $line;
                    $inUl = true;
                }
            }
            elseif ($line === '')
            {
                if ($inUl)
                {
                    $line = '</ul>';
                    $inUl = false;
                }
            }
        }

        return implode(PHP_EOL, $lines);
    }
}
