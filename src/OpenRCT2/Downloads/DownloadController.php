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
use Cyndaron\Util\Util;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use function nl2br;

final class DownloadController extends Controller
{
    private function fetchAndProcessBuilds(LoggerInterface $logger, string $name, string $url): Response
    {
        try
        {
            $builds = BuildLister::fetchAndProcessBuilds($url);

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
        return $this->fetchAndProcessBuilds($logger, 'Development builds', BuildLister::DEVELOPMENT_BUILDS_URL);
    }

    #[RouteAttribute('release', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function listReleaseBuilds(LoggerInterface $logger): Response
    {
        return $this->fetchAndProcessBuilds($logger, 'Release builds', BuildLister::RELEASE_BUILDS_URL);
    }

    #[RouteAttribute('changelog', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function showChangelog(): Response
    {
        $contents = Util::fetch('https://raw.githubusercontent.com/OpenRCT2/OpenRCT2/develop/distribution/changelog.txt');
        $page = new SimplePage('Changelog', nl2br($contents));
        return $this->pageRenderer->renderResponse($page);
    }
}
