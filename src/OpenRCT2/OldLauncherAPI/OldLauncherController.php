<?php
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\OldLauncherAPI;

use Cyndaron\OpenRCT2\Downloads\APICall;
use Cyndaron\OpenRCT2\Downloads\BuildLister;
use Cyndaron\OpenRCT2\Downloads\Classification\Architecture;
use Cyndaron\OpenRCT2\Downloads\Classification\OperatingSystem;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use function str_contains;
use function explode;
use function str_replace;
use function str_pad;
use function strrpos;
use function substr;
use function pow;
use function parse_url;
use function assert;

final class OldLauncherController
{
    #[RouteAttribute('', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function endpoint(Request $request): JsonResponse
    {
        $parsedUrl = parse_url($request->getRequestUri());
        $queryParts = explode('&', $parsedUrl['query'] ?? '');
        $queryParams = [];
        foreach ($queryParts as $queryPart)
        {
            $parts = explode('=', $queryPart);
            $queryParams[$parts[0]] = ($parts[1] ?? '');
        }
        $command = $queryParams['command'] ?? '';
        if ($command !== 'get-latest-download')
        {
            return new JsonResponse();
        }
        $flavourId = (int)($queryParams['flavourId'] ?? '');
        $flavour = Flavour::from($flavourId);

        $gitBranch = $queryParams['gitBranch'] ?? '';
        $releaseType = GitBranch::from($gitBranch);

        switch ($flavour)
        {
            case Flavour::WINDOWS_X86_32:
                $os = OperatingSystem::WINDOWS;
                $arch = Architecture::X86_32;
                $type = \Cyndaron\OpenRCT2\Downloads\Classification\Type::PORTABLE;
                break;
            case Flavour::WINDOWS_X86_64:
                $os = OperatingSystem::WINDOWS;
                $arch = Architecture::X86_64;
                $type = \Cyndaron\OpenRCT2\Downloads\Classification\Type::PORTABLE;
                break;
            case Flavour::LINUX_X86_32:
                $os = OperatingSystem::LINUX;
                $arch = Architecture::X86_32;
                $type = \Cyndaron\OpenRCT2\Downloads\Classification\Type::PORTABLE;
                break;
            case Flavour::LINUX_X86_64:
                $os = OperatingSystem::LINUX;
                $arch = Architecture::X86_64;
                $type = \Cyndaron\OpenRCT2\Downloads\Classification\Type::PORTABLE;
                break;
            case Flavour::MACOS:
                $os = OperatingSystem::MACOS;
                $arch = Architecture::UNIVERSAL;
                $type = \Cyndaron\OpenRCT2\Downloads\Classification\Type::PACKAGE;
                break;
            default:
                return new JsonResponse();
        }

        $call = $releaseType === GitBranch::RELEASE ? APICall::LATEST_RELEASE_BUILD : APICall::LATEST_DEVELOP_BUILD;
        $build = BuildLister::fetchAndProcessSingleBuild($call);
        $foundArtifact = null;
        foreach ($build->artifacts as $artifact)
        {
            if ($artifact->operatingSystem === $os
                && $artifact->architecture === $arch
            && $artifact->type === $type && !str_contains($artifact->operatingSystemVersion, 'bookworm'))
            {
                $foundArtifact = $artifact;
                break;
            }
        }

        if ($foundArtifact === null)
        {
            return new JsonResponse();
        }

        if ($releaseType === GitBranch::RELEASE)
        {
            $parts = explode('.', str_replace('v', '', $build->version));
            $number = 0;
            foreach ($parts as $index => $value)
            {
                if ($index >= 4)
                {
                    break;
                }
                $exponent = 6 - ($index * 2);
                $number += ((int)$value * pow(10, $exponent));
            }
            $shortCommit = str_pad((string)$number, 7, '0', STR_PAD_LEFT);
        }
        else
        {
            $lastDash = strrpos($build->version, '-');
            assert($lastDash !== false);
            $shortCommit = substr($build->version, $lastDash + 2);
        }

        $ret = [
          ///'buildId' => release.id,
          'downloadId' => "{$foundArtifact->version}-{$gitBranch}-{$shortCommit}",
          'fileSize' => $foundArtifact->size,
          'url' => $foundArtifact->downloadLink,
          'fileHash' => '',
          'gitHash' => $shortCommit,
          'gitHashShort' => $shortCommit,
          'addedTime' => $foundArtifact->publishedAt->format('U') * 1000,
          'addedTimeUnix' => (int)$foundArtifact->publishedAt->format('U'),
        ];
        return new JsonResponse($ret);
    }
}
