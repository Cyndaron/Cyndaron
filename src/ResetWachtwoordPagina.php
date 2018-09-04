<?php
namespace Cyndaron;

use Cyndaron\Widget\GoedeMelding;

require_once __DIR__ . '/../check.php';

class ResetWachtwoordPagina extends Pagina
{
    const MAIL_TEXT =
'<p>U vroeg om een nieuw wachtwoord voor %s.</p>

<p>Uw nieuwe wachtwoord is: %s</p>';

    const MAIL_HEADERS = <<<EOT
MIME-Version: 1.0
Content-type: text/html; charset=utf-8
From: %s <noreply@%s>
EOT;

    public function __construct()
    {
        parent::__construct('Wachtwoord resetten');
        parent::toonPrepagina();

        $pdo = DBConnection::getPdo();

        if (!Request::postIsLeeg())
        {
            $uid = Request::geefPostVeilig('uid');

            $user = DBConnection::getInstance()->doQueryAndFetchFirstRow('SELECT * FROM gebruikers WHERE id = ?', [$uid]);

            $newPassword = $this->generatePassword();
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

            $prep = $pdo->prepare('UPDATE gebruikers SET wachtwoord=? WHERE id =?');
            $prep->execute([$passwordHash, $user['id']]);

            $websiteName = Instelling::geefInstelling('websitenaam');
            $domain = str_replace("www.", "", $_SERVER['HTTP_HOST']);
            $domain = str_replace("http://", "", $domain);
            $domain = str_replace("https://", "", $domain);
            $domain = str_replace("/", "", $domain);

            mail(
                $user['email'],
                'Nieuw wachtwoord ingesteld',
                sprintf(self::MAIL_TEXT, $websiteName, $newPassword),
                sprintf(self::MAIL_HEADERS, $websiteName, $domain)
            );

            echo new GoedeMelding('Nieuw wachtwoord ingesteld voor ' . $user['gebruikersnaam'] . '.');
        }

        ?>
        <form method="post">
            <label for="uid">Gebruiker: </label>
            <select id="uid" name="uid">
                <?php
                $users = DBConnection::getInstance()->doQueryAndFetchAll('SELECT * FROM gebruikers ORDER BY gebruikersnaam', []);
                foreach ($users as $user)
                {
                    printf('<option value="%d">%s</option>', $user['id'], $user['gebruikersnaam']);
                }
                ?>
            </select>
            <input type="submit" value="Nieuw wachtwoord instellen"/>
        </form>
        <?php

        parent::toonPostPagina();
    }

    function generatePassword(): string
    {
        $gencode = '';
        $letters = ['a', 'c', 'd', 'e', 'f', 'h', 'j', 'm', 'n', 'q', 'r', 't',
            'A', 'C', 'D', 'E', 'F', 'H', 'J', 'L', 'M', 'N', 'Q', 'R', 'T',
            '3', '4', '7', '8'];

        for ($c = 0; $c < 10; $c++)
        {
            $gencode .= $letters[rand(0, count($letters))];
        }

        return $gencode;
    }
}