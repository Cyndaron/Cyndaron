<?php
namespace Cyndaron;

class Instelling
{
    protected $naam;
    protected $waarde;

    public function __construct(string $naam)
    {
        $this->naam = $naam;
    }

    public function geefWaarde($escape = false)
    {

    }

    public function opslaan()
    {
        $connectie = DBConnection::getPDO();
        $setting = $connectie->prepare('DELETE FROM instellingen WHERE naam= ?');
        $setting->execute([$this->naam]);
        $setting = $connectie->prepare('INSERT INTO instellingen(`waarde`,`naam`) VALUES (?, ?)');
        $setting->execute([$this->waarde, $this->naam]);
    }

    public static function geefInstelling($naam, $escape = false)
    {
        $connectie = DBConnection::getPDO();
        $setting = $connectie->prepare('SELECT waarde FROM instellingen WHERE naam= ?');
        $setting->execute([$naam]);
        if (!$escape)
        {
            return $setting->fetchColumn();
        }

        return htmlspecialchars($setting->fetchColumn(), ENT_COMPAT | ENT_HTML5, 'UTF-8', false);
    }

    public static function maakInstelling($naam, $waarde)
    {
        $connectie = DBConnection::getPDO();
        $setting = $connectie->prepare('DELETE FROM instellingen WHERE naam= ?');
        $setting->execute([$naam]);
        $setting = $connectie->prepare('INSERT INTO instellingen(`waarde`,`naam`) VALUES (?, ?)');
        $setting->execute([$waarde, $naam]);
    }
}