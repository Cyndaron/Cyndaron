<?php
namespace Cyndaron;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\DBAL\DatabaseError;
use Cyndaron\Error\IncompleteData;
use Cyndaron\Module\UrlProvider;

use function Safe\class_implements;
use function trim;
use function explode;
use function array_key_exists;
use function in_array;

final class Url
{
    private string $url;
    /** @var string[] $urlProviders */
    protected static array $urlProviders = [];

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getFriendly(): string
    {
        if ($friendly = DBConnection::doQueryAndFetchOne('SELECT name FROM friendlyurls WHERE target=?', [$this->url]))
        {
            return '/' . $friendly;
        }

        return $this->url;
    }

    public function getUnfriendly(): string
    {
        if ($unfriendly = DBConnection::doQueryAndFetchOne('SELECT target FROM friendlyurls WHERE name=?', [trim($this->url, '/')]))
        {
            return $unfriendly;
        }

        return $this->url;
    }

    public function equals(Url $otherUrl): bool
    {
        $url1 = $this->getUnfriendly();
        $url2 = $otherUrl->getUnfriendly();

        return $url1 === $url2;
    }

    public function __toString(): string
    {
        return $this->url;
    }

    public function createFriendly(string $name): void
    {
        if ($name === '' || $this->url === '')
        {
            throw new IncompleteData('Cannot create friendly URL with no name or no URL!');
        }
        if (DBConnection::doQuery('INSERT INTO friendlyurls(name,target) VALUES (?,?)', [$name, $this->url]) === false)
        {
            throw new DatabaseError('Could not insert friendly URL! Is the URL unique?');
        }
    }

    public function getPageTitle(): string
    {
        $link = trim($this->getUnfriendly(), '/');
        $linkParts = explode('/', $link);

        if (array_key_exists($linkParts[0], static::$urlProviders))
        {
            $classname = static::$urlProviders[$linkParts[0]];
            /** @var UrlProvider $class */
            $class = new $classname();
            $result = $class->url($linkParts);

            if ($result !== null)
            {
                return $result;
            }
        }

        if ($name = DBConnection::doQueryAndFetchOne('SELECT name FROM friendlyurls WHERE target=?', [$link]))
        {
            return $name;
        }

        return $link;
    }

    public static function addUrlProvider(string $urlBase, string $class): void
    {
        if (in_array(UrlProvider::class, class_implements($class), true))
        {
            static::$urlProviders[$urlBase] = $class;
        }
    }
}
