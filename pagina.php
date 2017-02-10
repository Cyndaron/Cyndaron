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
        $isadmin = isAdmin();
        $websitenaam = geefInstelling('websitenaam');
        $ondertitel = geefInstelling('ondertitel');
		?>
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo $this->paginanaam . ' - ' . $websitenaam;?></title>
        <?php
        echo '<link href="/sys/css/normalize.css" type="text/css" rel="stylesheet" />';
        echo '<link href="/sys/css/bootstrap.css" type="text/css" rel="stylesheet" />';
        echo '<link href="/sys/css/cyndaron.css" type="text/css" rel="stylesheet" />';
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
		<nav class="menu navbar navbar-inverse">
          <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
              <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="#">' . $websitenaam . '</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav">';

        $menuarray = geefMenu();
        if (count($menuarray) > 0)
        {
            foreach($menuarray as $menuitem)
            {
                // Vergelijking na || betekent testen of de hoofdurl is opgevraagd
                if ($menuitem['link'] == basename(substr($_SERVER['REQUEST_URI'], 1)) || ($menuitem['link'] == './' && substr($_SERVER['REQUEST_URI'], -1) == '/'))
                    echo '<li class="active">';
                else
                    echo '<li>';

                echo '<a href="' . $menuitem['link'] . '">' . $menuitem['naam'] . '</a></li>';
            }
        }

        echo '</ul>
              <ul class="nav navbar-nav navbar-right">';

        if (isAdmin())
        {
            printf('<p class="navbar-text">Ingelogd als %s</p>', $_SESSION['naam']);

            echo '
                    <li><a title="Uitloggen" href="logoff.php"><span class="glyphicon glyphicon-log-out"></span></a></li>
                    <li><a title="Instellingen aanpassen" href="configuratie.php"><span class="glyphicon glyphicon-cog"></span></a></li>
                    <li><a title="Paginaoverzicht" href="overzicht.php"><span class="glyphicon glyphicon-th-list"></span></a></li>
                    <li><a title="Nieuwe pagina aanmaken" href="editor.php?type=sub"><span class="glyphicon glyphicon-plus"></span></a></li>
                ';
        }
        else
        {
            echo '<li><a title="Inloggen" href="login.php"><span class="glyphicon glyphicon-lock"></span></a></li>';
        }

        echo '
              </ul>
            </div><!-- /.navbar-collapse -->
          </div><!-- /.container-fluid -->
        </nav>';


        $meldingen = geefMeldingen();
		if ($meldingen)
		{
            echo '<div class="meldingencontainer">';
			echo '<div class="meldingen alert alert-info"><ul>';

			foreach ($meldingen as $melding)
			{
				echo '<li>'.$melding.'</li>';
			}

			echo '</ul></div></div>';
		}

		echo '</div><div class="inhoudcontainer"><div class="inhoud"><div class="paginatitel"><h1 style="display: inline; margin-right:8px;">'.$this->paginanaam.'</h1>';
//		toonIndienAanwezigEnAdmin($this->titelknoppen, '<span style="vertical-align: middle; margin-bottom: 15px; padding-bottom: 15px;">', '</span>');
        toonIndienAanwezigEnAdmin($this->titelknoppen, '<div class="btn-group" style="vertical-align: bottom; margin-bottom: 3px;">', '</div>');
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