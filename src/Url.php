<?php
namespace Cyndaron;

class Url
{
    private $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function geefFriendly(): string
    {
        if ($friendly = DBConnection::doQueryAndFetchOne('SELECT naam FROM friendlyurls WHERE doel=?', [$this->url]))
        {
            return '/' . $friendly;
        }
        else
        {
            return $this->url;
        }
    }

    public function geefUnfriendly(): string
    {
        if ($unfriendly = DBConnection::doQueryAndFetchOne('SELECT doel FROM friendlyurls WHERE naam=?', [$this->url]))
        {
            return $unfriendly;
        }
        else
        {
            return $this->url;
        }
    }

    public function isGelijkAan(Url $andereUrl): bool
    {
        $url1 = $this->geefUnfriendly();
        $url2 = $andereUrl->geefUnfriendly();

        if ($url1 == $url2)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function __toString(): string
    {
        return $this->url;
    }

    public function maakFriendly(string $name)
    {
        if ($name == '' || $this->url == '')
            throw new \Exception('Cannot create friendly URL with no name or no URL!');
        DBConnection::doQuery('INSERT INTO friendlyurls(naam,doel) VALUES (?,?)', [$name, $this->url]);
    }

    public static function verwijderFriendlyUrl(string $naam)
    {
        DBConnection::doQuery('DELETE FROM friendlyurls WHERE naam=?', [$naam]);
    }

    public function geefPaginanaam(): string
    {
        $link = trim($this->geefUnfriendly(), '/');
        $linkParts = explode('/', $link);

        switch ($linkParts[0])
        {
            case 'sub':
                $sql = 'SELECT naam FROM subs WHERE id=?';
                break;
            case 'category':
                if ($linkParts[1] == 'fotoboeken')
                {
                    return 'Fotoalbums';
                }
                else
                {
                    $sql = 'SELECT naam FROM categorieen WHERE id=?';
                }
                break;
            case 'photoalbum':
                $sql = 'SELECT naam FROM fotoboeken WHERE id=?';
                break;
            default:
                return $link;
        }
        if ($naam = DBConnection::doQueryAndFetchOne($sql, [$linkParts[1]]))
        {
            return $naam;
        }
        elseif ($naam = DBConnection::doQueryAndFetchOne('SELECT naam FROM friendlyurls WHERE doel=?', [$link]))
        {
            return $naam;
        }
        else
        {
            return $link;
        }
    }
}