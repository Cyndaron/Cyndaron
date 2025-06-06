<?php
/**
 * Copyright © 2009-2024 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\Downloads;

use Cyndaron\Error\ErrorPage;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Psr\Log\LoggerInterface;
use ReflectionEnum;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use function preg_replace;
use function assert;

final class DownloadController
{
    public function __construct(
        protected readonly PageRenderer $pageRenderer,
    ) {
    }

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

    #[RouteAttribute('launcher', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function listLauncherBuilds(LoggerInterface $logger): Response
    {
        return $this->fetchAndProcessBuilds($logger, 'Launcher', APICall::LAUNCHER_BUILDS);
    }

    #[RouteAttribute('changelog', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function showChangelog(QueryBits $queryBits): Response
    {
        $factory = new ChangelogPageFactory(new APIFetcher());
        if ($queryBits->hasIndex(2))
        {
            $buildType = BuildType::from($queryBits->getString(2));
            $version = preg_replace('/[^0-9a-z\.\-]/', '', $queryBits->getString(3));
            assert($version !== null);
            $response = $factory->getPageForSpecificChangelog($buildType, $version);

        }
        else
        {
            $response = $factory->getPageForGeneralChangelog();
        }

        return $this->pageRenderer->renderResponse($response);
    }

    #[RouteAttribute('clearCache', RequestMethod::POST, UserLevel::ADMIN)]
    public function clearCache(QueryBits $queryBits): Response
    {
        $apiCallName = $queryBits->getString(2);
        $reflection = new ReflectionEnum(APICall::class);
        /** @var APICall $apiCall */
        $apiCall = $reflection->getCase($apiCallName)->getValue();
        $filename = $apiCall->getCachePath();
        \Safe\unlink($filename);
        return new RedirectResponse('/pagemanager/apicall');
    }
}
