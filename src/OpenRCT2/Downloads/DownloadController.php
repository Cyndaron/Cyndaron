<?php
/**
 * Copyright © 2009-2024 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\Downloads;

use Cyndaron\Error\ErrorPage;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\Controller;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use function explode;
use function str_starts_with;
use function substr;
use function preg_replace;
use function implode;

final class DownloadController extends Controller
{
    private function fetchAndProcessBuilds(LoggerInterface $logger, string $name, APICall $call): Response
    {
        try
        {
            $builds = BuildLister::fetchAndProcessBuilds($call);

            $page = new DownloadPage($name, $builds);
            return $this->pageRenderer->renderResponse($page);
        }
        catch (\Throwable $e)
        {
            $logger->error('Error retrieving builds: ' . $e);
            return $this->pageRenderer->renderErrorResponse(new ErrorPage($name, 'Could not retrieve list!'));
        }
    }

    #[RouteAttribute('develop', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function listDevelopmentBuilds(LoggerInterface $logger): Response
    {
        return $this->fetchAndProcessBuilds($logger, 'Development builds', APICall::DEVELOP_BUILDS);
    }

    #[RouteAttribute('release', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function listReleaseBuilds(LoggerInterface $logger): Response
    {
        return $this->fetchAndProcessBuilds($logger, 'Release builds', APICall::RELEASE_BUILDS);
    }

    #[RouteAttribute('changelog', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function showChangelog(): Response
    {
        $fetcher = new APIFetcher();
        $contents = $fetcher->fetch(APICall::CHANGELOG);

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
                $line = preg_replace('/([A-Za-z]+)#([0-9]+)/', '<a href="https://github.com/OpenRCT2/$1/issues/$2">$1#$2</a>', $line);
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

        $contents = implode(PHP_EOL, $lines);
        $page = new SimplePage('Changelog', $contents);
        return $this->pageRenderer->renderResponse($page);
    }
}
