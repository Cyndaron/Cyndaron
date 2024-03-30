<?php
declare(strict_types=1);

namespace Cyndaron\Url;

use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\DatabaseError;
use Cyndaron\Module\UrlProvider;
use Cyndaron\Util\Error\IncompleteData;
use function array_key_exists;
use function explode;
use function is_string;
use function trim;

class UrlService
{
    /**
     * @param class-string<UrlProvider>[] $urlProviders
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly string $requestUri,
        private readonly array $urlProviders
    ) {
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
            $result = $class->url($linkParts);

            if ($result !== null)
            {
                return $result;
            }
        }

        if ($name = $this->connection->doQueryAndFetchOne('SELECT name FROM friendlyurls WHERE target=?', [$link]))
        {
            return $name;
        }

        return $link;
    }

    public function toFriendly(Url|string $url): Url
    {
        if ($friendly = $this->connection->doQueryAndFetchOne('SELECT name FROM friendlyurls WHERE target=?', [(string)$url]))
        {
            return new Url('/' . $friendly);
        }

        return is_string($url) ? new Url($url) : $url;
    }

    public function toUnfriendly(Url|string $url): Url
    {
        if ($unfriendly = $this->connection->doQueryAndFetchOne('SELECT target FROM friendlyurls WHERE name=?', [trim((string)$url, '/')]))
        {
            return new Url($unfriendly);
        }

        return is_string($url) ? new Url($url) : $url;
    }

    public function equals(Url|string $url1, Url|string $url2): bool
    {
        $url1 = $this->toUnfriendly($url1);
        $url2 = $this->toUnfriendly($url2);
        return (string)$url1 === (string)$url2;
    }

    public function createFriendlyUrl(Url|string $unfriendlyUrl, string $name): void
    {
        if ($name === '' || (string)$unfriendlyUrl === '')
        {
            throw new IncompleteData('Cannot create friendly URL with no name or no URL!');
        }
        if ($this->connection->insert('INSERT INTO friendlyurls(name,target) VALUES (?,?)', [$name, (string)$unfriendlyUrl]) === false)
        {
            throw new DatabaseError('Could not insert friendly URL! Is the URL unique?');
        }
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
}
