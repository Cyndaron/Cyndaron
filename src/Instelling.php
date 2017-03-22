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
        $connectie = newPDO();
        $setting = $connectie->prepare('DELETE FROM instellingen WHERE naam= ?');
        $setting->execute(array($this->naam));
        $setting = $connectie->prepare('INSERT INTO instellingen(`waarde`,`naam`) VALUES (?, ?)');
        $setting->execute(array($this->waarde, $this->naam));
    }
}