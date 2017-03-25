<?php
namespace Cyndaron;

class StatischePaginaModel
{
    protected $id = null;
    protected $naam = '';
    protected $tekst = '';
    protected $reactiesAan = false;
    protected $categorieId;


    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getNaam(): string
    {
        return $this->naam;
    }

    /**
     * @param string $naam
     */
    public function setNaam(string $naam)
    {
        $this->naam = $naam;
    }

    /**
     * @return string
     */
    public function getTekst(): string
    {
        return $this->tekst;
    }

    /**
     * @param mixed $tekst
     */
    public function setTekst(string $tekst)
    {
        $this->tekst = $tekst;
    }

    /**
     * @return bool
     */
    public function getReactiesAan(): bool
    {
        return $this->reactiesAan;
    }

    /**
     * @param mixed $reactiesAan
     */
    public function setReactiesAan(bool $reactiesAan)
    {
        $this->reactiesAan = $reactiesAan;
    }

    /**
     * @return mixed
     */
    public function getCategorieId(): int
    {
        return $this->categorieId;
    }

    /**
     * @param mixed $categorieId
     */
    public function setCategorieId(int $categorieId)
    {
        $this->categorieId = $categorieId;
    }

    public function __construct(int $id = null)
    {
        if ($id > 0)
        {
            $this->id = $id;
        }
    }

    public function laden(): bool
    {
        if ($this->id === null)
        {
            return false;
        }

        $connectie = DBConnection::getPDO();
        $prep = $connectie->prepare('SELECT * FROM subs WHERE id=?');
        $prep->execute([$this->id]);
        $record = $prep->fetch();

        $this->naam = $record['naam'];
        $this->tekst = $record['tekst'];
        $this->reactiesAan = $record['reacties_aan'] == 1 ? true : false;
        $this->categorieId = $record['categorieid'];
        return true;
    }

    /**
     * Slaat de statische pagina op
     *
     * @return int Het ID van de statische pagina.
     */
    public function opslaan(): int
    {
        if ($this->id === null)
        {
            if (!$this->reactiesAan)
            {
                $reacties_aan = '0';
            }
            else
            {
                $reacties_aan = '1';
            }

            $connectie = DBConnection::getPDO();
            $prep = $connectie->prepare('INSERT INTO subs(naam, tekst, reacties_aan, categorieid) VALUES ( ?, ?, ?, ?)');
            $prep->execute([$this->naam, $this->tekst, $reacties_aan, $this->categorieId]);
            $this->id = $connectie->lastInsertId();
            return $this->id;
        }
        else
        {
            $reacties_aan = Util::parseCheckboxAlsInt($this->reactiesAan);
            $connectie = DBConnection::getPDO();
            if (!DBConnection::geefEen('SELECT * FROM vorigesubs WHERE id=?', [$this->id]))
            {
                $prep = $connectie->prepare('INSERT INTO vorigesubs VALUES (?, \'\', \'\')');
                $prep->execute([$this->id]);
            }
            $prep = $connectie->prepare('UPDATE vorigesubs SET tekst=( SELECT tekst FROM subs WHERE id=? ) WHERE id=?');
            $prep->execute([$this->id, $this->id]);
            $prep = $connectie->prepare('UPDATE vorigesubs SET naam=( SELECT naam FROM subs WHERE id=? ) WHERE id=?');
            $prep->execute([$this->id, $this->id]);

            $prep = $connectie->prepare('UPDATE subs SET tekst= ?, naam= ?, reacties_aan=?, categorieid= ? WHERE id= ?');
            $prep->execute([$this->tekst, $this->naam, $reacties_aan, $this->categorieId, $this->id]);
            return $this->id;
        }
    }

    public function verwijder()
    {
        if ($this->id != null)
        {
            DBConnection::maakEen('DELETE FROM subs WHERE id=?;', [$this->id]);
            DBConnection::maakEen('DELETE FROM vorigesubs WHERE id=?;', [$this->id]);
        }
    }
}