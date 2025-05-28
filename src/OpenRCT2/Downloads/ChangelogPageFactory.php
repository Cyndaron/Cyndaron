<?php
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\Downloads;

use Cyndaron\Error\ErrorPage;
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
use function str_ends_with;
use function assert;
use function is_string;

final class ChangelogPageFactory
{
    public function __construct(private readonly APIFetcher $fetcher)
    {
    }

    public function getPageForGeneralChangelog(): Page
    {
        $contents = $this->fetcher->fetch(APICall::CHANGELOG);
        $page = new SimplePage('Changelog', $this->render($contents));
        return $page->toPage();
    }

    public function getPageForSpecificChangelog(BuildType $buildType, string $version): Page
    {
        switch ($buildType)
        {
            case BuildType::RELEASE:
                $builds = BuildLister::fetchAndProcessBuilds(APICall::RELEASE_BUILDS);
                $mainRepo = 'OpenRCT2/OpenRCT2';
                break;
            case BuildType::LAUNCHER:
                $builds = BuildLister::fetchAndProcessBuilds(APICall::LAUNCHER_BUILDS);
                $mainRepo = 'OpenRCT2/OpenLauncher';
                break;
            default:
                throw new \Exception('Cannot show changelog for these builds!');
        }

        $foundBuild = null;
        foreach ($builds as $build)
        {
            if ($build->version === $version)
            {
                $foundBuild = $build;
                break;
            }
        }

        if ($foundBuild === null)
        {
            $page = new ErrorPage('Version not found!', "Could not find version {$version}!");
            return $page->toPage();
        }

        $contents = str_replace("\r\n", "\n", $foundBuild->body); //nl2br($foundBuild->body);
        $page = new SimplePage("Changelog for {$version}", $this->render($contents, $mainRepo));
        return $page->toPage();
    }

    private function render(string $contents, string $mainRepo = 'OpenRCT2/OpenRCT2'): string
    {
        // Titles
        /** @var string $contents */
        $contents = preg_replace('/([0-9].*?)\n(----+\n)/', '<h2>$1</h2>' . "\n", $contents);
        $contents = preg_replace('/```([\s\S]+?)```/', '<pre>$1</pre>' . "\n", $contents);
        assert(is_string($contents));

        $lines = explode(PHP_EOL, $contents);
        $inUl = false;
        foreach ($lines as &$line)
        {
            if (str_starts_with($line, '- '))
            {
                $line = str_replace('<br>', '', $line);
                $line = '<li>' . substr($line, 2) . '</li>';
                /** @var string $line */
                $line = preg_replace('/([^A-Za-z])#([0-9]+)/', '$1<a href="https://github.com/' . $mainRepo . '/issues/$2">#$2</a>', $line);
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
            elseif (str_ends_with($line, ':'))
            {
                $line = "<h3>{$line}</h3>";
            }
        }

        return implode(PHP_EOL, $lines);
    }
}
