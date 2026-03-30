<?php
declare(strict_types=1);

namespace Cyndaron\Url;

use Closure;
use Cyndaron\Base\ModuleRegistry;
use Cyndaron\DBAL\Model;
use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\FriendlyUrl\FriendlyUrl;
use Cyndaron\FriendlyUrl\FriendlyUrlRepository;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\UrlProvider;
use Cyndaron\Util\Error\IncompleteData;
use Cyndaron\Util\Link;
use Symfony\Component\HttpFoundation\Request;
use function array_key_exists;
use function explode;
use function is_string;
use function trim;
use function property_exists;

class UrlService
{
    /**
     * @var class-string<UrlProvider>[]
     */
    private readonly array $urlProviders;

    /**
     * @var array<class-string<Model>, Datatype>
     */
    private readonly array $modelToDatatypes;

    private string $requestUri;

    public function __construct(
        private readonly FriendlyUrlRepository $friendlyUrlRepository,
        private readonly GenericRepository $genericRepository,
        Request $request,
        ModuleRegistry $registry,
    ) {
        $this->requestUri = $request->getRequestUri();
        $this->urlProviders = $registry->urlProviders;
        $this->modelToDatatypes = $registry->modelToDatatypes;
    }

    public function getPageTitle(Url|string $url): string
    {
        $link = trim((string)$this->toUnfriendly($url), '/');
        $linkParts = explode('/', $link);

        if (array_key_exists($linkParts[0], $this->urlProviders))
        {
            $classname = $this->urlProviders[$linkParts[0]];
            /** @var UrlProvider $class */
            $class = new $classname();
            $result = $class->nameFromUrl($this->genericRepository, $linkParts);

            if ($result !== null)
            {
                return $result;
            }
        }

        if ($friendly = $this->friendlyUrlRepository->fetchByTarget($link))
        {
            return $friendly->name;
        }

        return $link;
    }

    public function toFriendly(Url|string $url): Url
    {
        if ($friendly = $this->friendlyUrlRepository->fetchByTarget((string)$url))
        {
            return new Url('/' . $friendly->name);
        }

        return is_string($url) ? new Url($url) : $url;
    }

    public function toUnfriendly(Url|string $url): Url
    {
        if ($friendly = $this->friendlyUrlRepository->fetchByName((string)$url))
        {
            return new Url($friendly->target);
        }

        return is_string($url) ? new Url($url) : $url;
    }

    public function equals(Url|string $url1, Url|string $url2): bool
    {
        $url1 = $this->toUnfriendly($url1);
        $url2 = $this->toUnfriendly($url2);
        return (string)$url1 === (string)$url2;
    }

    /**
     * @todo Move to repository?
     */
    public function createFriendlyUrl(Url|string $unfriendlyUrl, string $name): void
    {
        if ($name === '' || (string)$unfriendlyUrl === '')
        {
            throw new IncompleteData('Cannot create friendly URL with no name or no URL!');
        }

        $model = new FriendlyUrl();
        $model->name = $name;
        $model->target = (string)$unfriendlyUrl;
        $this->friendlyUrlRepository->save($model);
    }

    public function isCurrentPage(Url|string $url): bool
    {
        $link = (string)$url;
        // The first comparison checks if the homepage has been requested.
        if (($link === '/' && $this->requestUri === '/') || $link === $this->requestUri)
        {
            return true;
        }

        return false;
    }

    public function getUrlForModel(Model $model): Url
    {
        $datatype = $this->modelToDatatypes[$model::class] ?? null;
        if ($datatype === null || $datatype->modelToUrl === null)
        {
            throw new \Exception('No url providers for this model!');
        }

        $closure = $datatype->modelToUrl;
        return $closure($model);
    }

    public function getFriendlyUrlForModel(Model $model): Url
    {
        return $this->toFriendly($this->getUrlForModel($model));
    }
}
