<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBConnection;

class StaticPageModel
{
    protected $id = null;
    protected $name = '';
    protected $text = '';
    protected $enableComments = false;
    protected $categoryId;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText(string $text)
    {
        $this->text = $text;
    }

    /**
     * @return bool
     */
    public function getEnableComments(): bool
    {
        return $this->enableComments;
    }

    /**
     * @param mixed $enableComments
     */
    public function setEnableComments(bool $enableComments)
    {
        $this->enableComments = $enableComments;
    }

    /**
     * @return mixed
     */
    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    /**
     * @param mixed $categoryId
     */
    public function setCategoryId(int $categoryId)
    {
        $this->categoryId = $categoryId;
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

        $connection = DBConnection::getPDO();
        $prep = $connection->prepare('SELECT * FROM subs WHERE id=?');
        $prep->execute([$this->id]);
        $record = $prep->fetch();

        if ($record === false)
        {
            return false;
        }

        $this->name = $record['naam'];
        $this->text = $record['tekst'];
        $this->enableComments = $record['reacties_aan'] == 1 ? true : false;
        $this->categoryId = $record['categorieid'];
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
            if (!$this->enableComments)
            {
                $reacties_aan = '0';
            }
            else
            {
                $reacties_aan = '1';
            }

            $connection = DBConnection::getPDO();
            $prep = $connection->prepare('INSERT INTO subs(naam, tekst, reacties_aan, categorieid) VALUES ( ?, ?, ?, ?)');
            $prep->execute([$this->name, $this->text, $reacties_aan, $this->categoryId]);
            $this->id = $connection->lastInsertId();
            return $this->id;
        }
        else
        {
            $reacties_aan = Util::parseCheckboxAlsInt($this->enableComments);
            $connection = DBConnection::getPDO();
            if (!DBConnection::doQueryAndFetchOne('SELECT * FROM vorigesubs WHERE id=?', [$this->id]))
            {
                $prep = $connection->prepare('INSERT INTO vorigesubs VALUES (?, \'\', \'\')');
                $prep->execute([$this->id]);
            }
            $prep = $connection->prepare('UPDATE vorigesubs SET tekst=( SELECT tekst FROM subs WHERE id=? ) WHERE id=?');
            $prep->execute([$this->id, $this->id]);
            $prep = $connection->prepare('UPDATE vorigesubs SET naam=( SELECT naam FROM subs WHERE id=? ) WHERE id=?');
            $prep->execute([$this->id, $this->id]);

            $prep = $connection->prepare('UPDATE subs SET tekst= ?, naam= ?, reacties_aan=?, categorieid= ? WHERE id= ?');
            $prep->execute([$this->text, $this->name, $reacties_aan, $this->categoryId, $this->id]);
            return $this->id;
        }
    }

    public function verwijder()
    {
        if ($this->id != null)
        {
            DBConnection::doQuery('DELETE FROM subs WHERE id=?;', [$this->id]);
            DBConnection::doQuery('DELETE FROM vorigesubs WHERE id=?;', [$this->id]);
        }
    }
}