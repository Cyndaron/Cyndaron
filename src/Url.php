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
        if ($friendly = geefEen('SELECT naam FROM friendlyurls WHERE doel=?', [$this->url]))
        {
            return $friendly;
        }
        else
        {
            return $this->url;
        }
    }

    public function geefUnfriendly(): string
    {
        if ($unfriendly = geefEen('SELECT doel FROM friendlyurls WHERE naam=?', [$this->url]))
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
            /*$hoofdurl=geefUnfriendlyUrl(geefEen('SELECT link FROM menu WHERE volgorde=(SELECT MIN(volgorde) FROM menu)',array()));
            if (($url1=='/' && $url2==$hoofdurl) || ($url2=='/' && $url1==$hoofdurl))
            {
                return true;
            }*/
            return false;
        }
    }

    public function __toString(): string
    {
        return $this->url;
    }

    public function maakFriendly(string $naam)
    {
        maakEen('INSERT INTO friendlyurls(naam,doel) VALUES (?,?)', array($naam, $this->url));
    }

    public static function verwijderFriendlyUrl(string $naam)
    {
        maakEen('DELETE FROM friendlyurls WHERE naam=?', array($naam));
    }

    public function geefPaginanaam(): string
    {
        $link = $this->geefUnfriendly();
        $pos = strrpos($link, '/', -1);
        $laatstedeel = substr($link, $pos);
        $split = explode('?', $laatstedeel);
        $vars = @explode('&', $split[1]);
        $values = null;
        foreach ($vars as $var)
        {
            $temp = explode('=', $var);
            $values[$temp[0]] = @$temp[1];
        }
        switch ($split[0])
        {
            case 'toonsub.php':
                $sql = 'SELECT naam FROM subs WHERE id=?';
                break;
            case 'tooncategorie.php':
                if ($values['id'] == 'fotoboeken')
                    return 'Fotoboeken';
                else
                    $sql = 'SELECT naam FROM categorieen WHERE id=?';
                break;
            case 'toonfotoboek.php':
                $sql = 'SELECT naam FROM fotoboeken WHERE id=?';
                break;
            default:
                return $link;
        }
        if ($naam = geefEen($sql, array($values['id'])))
            return $naam;
        elseif ($naam = geefEen('SELECT naam FROM friendlyurls WHERE link=?', array($link)))
            return $naam;
        else
            return $link;
    }
}