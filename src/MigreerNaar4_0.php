<?php
namespace Cyndaron;

require __DIR__ . '/../check.php';

class MigreerNaar4_0 extends Pagina
{
    public function __construct()
    {
        #migreer hoofdstukken naar subs
        $connectie = DBConnection::getPDO();
        $hoofdstukken = $connectie->prepare('SELECT * FROM hoofdstukken ORDER BY id ASC;');
        $hoofdstukken->execute();

        foreach ($hoofdstukken as $hoofdstuk)
        {
            $inhoud = "";
            $artikelen = $connectie->prepare('SELECT tekst FROM artikelen WHERE hid=? ORDER BY id DESC;');
            $artikelen->execute([$hoofdstuk['id']]);
            foreach ($artikelen as $artikel)
            {
                if ($inhoud != "")
                {
                    $inhoud .= '<br /><br />';
                }
                $inhoud .= $artikel['tekst'];
            }

            $id = $this->nieuweSub($hoofdstuk['naam'], $inhoud, 0, 0);
            MenuModel::voegToeAanMenu('toonsub.php?id=' . $id, "");

            $vorigeinhoud = "";
            $vorigeartikelen = $connectie->prepare('SELECT tekst FROM vorigeartikelen WHERE hid=? ORDER BY id ASC;');
            $vorigeartikelen->execute([$hoofdstuk['id']]);
            foreach ($vorigeartikelen as $vorigartikel)
            {
                if ($vorigeinhoud != "")
                {
                    $vorigeinhoud .= '<br /><br />';
                }
                $vorigeinhoud .= $vorigartikel['tekst'];
            }

            DBConnection::geefEen('INSERT INTO vorigesubs(id,naam,tekst) VALUES (?,?,?);', [$id, $hoofdstuk['naam'], $vorigeinhoud]);
        }

        $categorieen = $connectie->prepare('SELECT id FROM categorieen ORDER BY id ASC;');
        $categorieen->execute();

        foreach ($categorieen as $categorie)
        {
            MenuModel::voegToeAanMenu('tooncategorie.php?id=' . $categorie['id'], "");
        }

        if ($connectie->query('SELECT * FROM fotoboeken')->fetchColumn())
        {
            MenuModel::voegToeAanMenu('tooncategorie.php?id=fotoboeken', "");
        }

        $extramenuitems = $connectie->prepare('SELECT naam,link FROM vastemenuitems ORDER BY id ASC;');
        $extramenuitems->execute();
        foreach ($extramenuitems as $extramenuitem)
        {
            MenuModel::voegToeAanMenu($extramenuitem['link'], $extramenuitem['naam']);
        }

        DBConnection::geefEen('DROP TABLE vorigeartikelen', []);
        DBConnection::geefEen('DROP TABLE artikelen', []);
        DBConnection::geefEen('DROP TABLE hoofdstukken', []);
        DBConnection::geefEen('DROP TABLE vastemenuitems', []);

        parent::__construct('Upgrade naar versie 4.0');
        $this->toonPrepagina();
        echo 'De upgrade is voltooid.';
        $this->toonPostPagina();
    }

    private function nieuweSub($titel, $tekst, $reacties_aan, $categorieid)
    {
        if (!$reacties_aan)
            $reacties_aan = '0';
        else
            $reacties_aan = '1';

        $connectie = DBConnection::getPDO();
        $prep = $connectie->prepare('INSERT INTO subs(naam, tekst, reacties_aan, categorieid) VALUES ( ?, ?, ?, ?)');
        $prep->execute(array($titel, $tekst, $reacties_aan, $categorieid));
        return $connectie->lastInsertId();
    }
}