<?php
require_once('functies.url.php');
require_once('functies.db.php');
require_once('functies.pagina.php');
require_once('functies.gebruikers.php');
// Verwijs oude URLs door

if (!empty($_GET['friendlyurls'])  && $url=geefEen('SELECT naam FROM friendlyurls WHERE doel=?', array(basename(substr($_SERVER['REQUEST_URI'],1)))))
{
	header('Location: '.$url);
}

if (empty($_SESSION))
{
	session_start();
}

class Pagina
{
    private $extraMeta = "";
    private $paginanaam = "";
    private $titelknoppen = null;
    private $connectie = null;
    private $nietDelen = false;

	// Gebruik met tweede parameter is deprecated
    public function __construct($paginanaam, $titelcontrols = null)
	{
		$this->paginanaam=$paginanaam;
		$this->maakTitelknoppen($titelcontrols);
	}

    public function maakExtraMeta($extraMeta)
    {
        $this->extraMeta = $extraMeta;
    }

    public function maaknietDelen($bool)
    {
        $this->nietDelen = (bool)$bool;
    }

    public function maakTitelknoppen($titelknoppen)
    {
        $this->titelknoppen = $titelknoppen;
    }

	public function toonPrepagina()
	{
		$isadmin=isAdmin();
		$websitenaam=geefInstelling('websitenaam');
		$ondertitel=geefInstelling('ondertitel');
		?>
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo $this->paginanaam . ' - ' . $websitenaam;?></title>
        <?php
		echo '<link href="/sys/css/stijl.css" type="text/css" rel="stylesheet" />';
        echo '<link href="/user.css" type="text/css" rel="stylesheet" />';
        if ($favicon = geefInstelling('favicon'))
        {
            $extensie = substr(strrchr($favicon, "."), 1);
            echo '	<link rel="icon" type="image/' . $extensie . '" href="' . $favicon . '">';
        }
        ?>
		<style type="text/css">
            <?php
            toonIndienAanwezig(geefInstelling('achtergrondkleur'), 'body { background-color: ',";}\n");
            toonIndienAanwezig(geefInstelling('menukleur'), '.menu { background-color: ',";}\n");
            toonIndienAanwezig(geefInstelling('menuachtergrond'), '.menu { background-image: url(\'',"');}\n");
            toonIndienAanwezig(geefInstelling('artikelkleur'), '.inhoud { background-color: ',";}\n");
            ?>
        </style>
        <script type="text/javascript" src="/sys/js/email-antispam.js"></script>
		<script type="text/javascript">
		function geefInstelling(instelling)
		{
			if (instelling == 'artikelkleur')
			{
                return '<?php echo geefInstelling('artikelkleur');?>';
			}
		}
		</script>
		</head>
		<body><?php
        if ($this->nietDelen == false)
		{
			toonIndienAanwezig(geefInstelling('extra_bodycode'));
			if (geefInstelling('facebook_share')==1)
			{
				echo '<div id="fb-root"></div>
				<script type="text/javascript" src="/sys/js/facebook-like.js"></script>';
			}
		}
		echo '
		<div class="paginacontainer">
		<div class="menucontainer">
		<div class="menu">
		<h1>'.$websitenaam.'</h1>'.$ondertitel;
		if ($ondertitel && $isadmin)
		{
			echo ' - ';
		}

        if (!empty($_SESSION) && !empty($_SESSION['naam']))
        {
            toonIndienAanwezigEnAdmin('Ingelogd als '.$_SESSION['naam'].' - <a href="logoff.php">Uitloggen</a>');
            toonIndienAanwezigEnAdmin(knopcode('instellingen.png', 'configuratie.php','Instellingen aanpassen'),' ','');
            toonIndienAanwezigEnAdmin(knopcode('lijst.png', 'overzicht.php','Paginaoverzicht'),' ','');
            toonIndienAanwezigEnAdmin(knopcode('nieuw', "editor.php?type=sub", 'Nieuwe sub aanmaken'),' ','');
        }

		echo '<div class="dottop"><ul class="menulijst">';
        $menuarray = geefMenu();
		foreach($menuarray as $menuitem)
		{
            // Vergelijking na || betekent testen of de hoofdurl is opgevraagd
            if ($menuitem['link'] == basename(substr($_SERVER['REQUEST_URI'], 1)) || ($menuitem['link'] == './' && substr($_SERVER['REQUEST_URI'], -1) == '/'))
            {
                echo '<li>' . $menuitem['naam'] . "</li>\n";
            }
            else
            {
                echo '<li><a href="' . $menuitem['link'] . '">' . $menuitem['naam'] . "</a></li>\n";
            }
        }
		toonIndienAanwezigEnGeenAdmin('<li><span class="small"><a href="login.php">L </a></span></li>');

        $paneel = geefInstelling('paneel');
		echo '</ul></div>';
		toonIndienAanwezig($paneel, '<div class="dottop">', "</div>\n");

		//Meldingen:
        $meldingen = geefMeldingen();
		if ($meldingen)
		{
			echo '<div style="display: inline-block; border-radius: 3px; padding: 3px; border: 1px dotted #333333; background-color: #EEEEEE;"><ul style="margin: 0px; padding-left: 10px;">';

			foreach ($meldingen as $melding)
			{
				echo '<li style="font-size: 11px;">'.$melding.'</li>';
			}
			echo '</ul></div>';
		}

		echo '</div></div><div class="inhoudcontainer"><div class="inhoud"><div class="paginatitel"><h1 style="display: inline; margin-right:8px;">'.$this->paginanaam.'</h1>';
		toonIndienAanwezigEnAdmin($this->titelknoppen, '<span style="vertical-align: middle; margin-bottom: 15px; padding-bottom: 15px;">', '</span>');
		echo "</div>\n";
	}

    public function toonPostPagina()
    {
        if ($this->nietDelen == false)
        {
            toonDeelknoppen();
        }
        // Eerste div: inhoud. Tweede div: inhoudcontainer. Derde div: paginacontainer
        echo "</div></div></div>\n</body>\n</html>";
    }
}