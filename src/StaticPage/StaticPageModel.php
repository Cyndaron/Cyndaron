<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBConnection;
use Cyndaron\Model;

class StaticPageModel extends Model
{
    protected $name = '';
    protected $text = '';
    protected $enableComments = false;
    protected $categoryId;
    protected static $table = 'subs';

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
            $reacties_aan = (int)(bool)($this->enableComments);
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

    public function delete(): void
    {
        parent::delete();
        DBConnection::doQuery('DELETE FROM vorige' . static::$table . ' WHERE id = ?', [$this->id]);
    }

    public function react(string $auteur, string $reactie, string $antispam)
    {
        if ($this->id == null)
        {
            throw new \Exception('No ID!');
        }
        $this->laden();
        if ($this->getEnableComments())
        {
            if ($auteur && $reactie && ($antispam == 'acht' || $antispam == '8'))
            {
                $datum = date('Y-m-d H:i:s');
                $prep = DBConnection::getPdo()->prepare('INSERT INTO reacties(subid, auteur, tekst, datum) VALUES (?, ?, ?, ?)');
                $prep->execute([$this->id, $auteur, $reactie, $datum]);
            }
        }
    }
}