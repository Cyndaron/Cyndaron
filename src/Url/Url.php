<?php
namespace Cyndaron\Url;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\DBAL\DatabaseError;
use Cyndaron\DBAL\DBConnection;
use Cyndaron\Module\UrlProvider;
use Cyndaron\Util\Error\IncompleteData;
use function array_key_exists;
use function explode;
use function trim;

final class Url
{
    private string $url;
    // TODO Refactor class into factory
    private static ModuleRegistry $registry;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getFriendly(): string
    {
        if ($friendly = DBConnection::getPDO()->doQueryAndFetchOne('SELECT name FROM friendlyurls WHERE target=?', [$this->url]))
        {
            return '/' . $friendly;
        }

        return $this->url;
    }

    public function getUnfriendly(): string
    {
        if ($unfriendly = DBConnection::getPDO()->doQueryAndFetchOne('SELECT target FROM friendlyurls WHERE name=?', [trim($this->url, '/')]))
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
        if (DBConnection::getPDO()->insert('INSERT INTO friendlyurls(name,target) VALUES (?,?)', [$name, $this->url]) === false)
        {
            throw new DatabaseError('Could not insert friendly URL! Is the URL unique?');
        }
    }

    public function getPageTitle(): string
    {
        $link = trim($this->getUnfriendly(), '/');
        $linkParts = explode('/', $link);

        if (array_key_exists($linkParts[0], self::$registry->urlProviders))
        {
            $classname = self::$registry->urlProviders[$linkParts[0]];
            /** @var UrlProvider $class */
            $class = new $classname();
            $result = $class->url($linkParts);

            if ($result !== null)
            {
                return $result;
            }
        }

        if ($name = DBConnection::getPDO()->doQueryAndFetchOne('SELECT name FROM friendlyurls WHERE target=?', [$link]))
        {
            return $name;
        }

        return $link;
    }

    public static function setRegistry(ModuleRegistry $registry): void
    {
        self::$registry = $registry;
    }
}
