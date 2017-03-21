<?php
require_once('functies.db.php');
require_once('functies.kaartverkoop.php');
require_once('pagina.php');

if(postIsLeeg() || empty(geefPostVeilig('concert_id')))
{
	$pagina = new Pagina('Bestelling niet verwerkt');
	$pagina->toonPrePagina();
	echo 'De bestellingsgegevens zijn niet goed aangekomen.';
	$pagina->toonPostPagina();
	die();
}

$connectie = newPDO();
$concert_id = geefPostVeilig('concert_id');
$postcode = geefPostVeilig('postcode');
$buitenland = geefPostVeilig('buitenland') ? true : false;
$ophalenDoorKoorlid = geefPostVeilig('ophalen_door_koorlid') ? true : false;
$ophalenDoorKoorlid = $buitenland ? true : $ophalenDoorKoorlid;

$concertquery = "SELECT * FROM `kaartverkoop_concerten` WHERE id=?";
$prep=$connectie->prepare($concertquery);
$prep->execute(array($concert_id));
$concert=$prep->fetch();

$incorrecteVelden=checkFormulier($concert['bezorgen_verplicht'], $ophalenDoorKoorlid);

if(!empty($incorrecteVelden))
{
	$pagina=new Pagina('Bestelling niet verwerkt');
	$pagina->toonPrePagina();
	echo 'De volgende velden zijn niet goed ingevuld of niet goed aangekomen: ';
	echo implode (', ', $incorrecteVelden).'.';

	$pagina->toonPostPagina();
	die();
}

$totaalprijs = 0;
$totaalAantalKaarten = 0;

if ($concert['bezorgen_verplicht'])
{
	$woontInWalcheren = ($buitenland) ? false : postcodeLigtInWalcheren($postcode);

	if ($woontInWalcheren)
	{
		$bezorgen = false;
		$ophalenDoorKoorlid = false;
		$naam_koorlid = '';
	}
	else
	{
		if ($ophalenDoorKoorlid)
		{
			$bezorgen = false;
			$naam_koorlid = geefPostVeilig('naam_koorlid');
		}
		else
		{
			$bezorgen = true;
			$naam_koorlid = '';
		}
	}
}
else
{
	$bezorgen = geefPostVeilig('bezorgen') ? true : false;
}
$bezorgprijs = $bezorgen ? $concert['verzendkosten'] : 0;
$gereserveerde_plaatsen = geefPostVeilig('gereserveerde_plaatsen') ? true : false;
$toeslag_gereserveerde_plaats = $gereserveerde_plaatsen ? $concert['toeslag_gereserveerde_plaats'] : 0;
$bestelling_kaartsoorten = array();
$prep = $connectie->prepare('SELECT * FROM kaartverkoop_kaartsoorten WHERE concert_id=? ORDER BY prijs DESC');
$prep->execute(array($concert_id));
$kaartsoorten = $prep->fetchAll();
foreach($kaartsoorten as $kaartsoort)
{
	$bestelling_kaartsoorten[$kaartsoort['id']] = intval(geefPostVeilig('kaartsoort-'.$kaartsoort['id']));
	$totaalprijs += $bestelling_kaartsoorten[$kaartsoort['id']]*($kaartsoort['prijs']+$bezorgprijs+$toeslag_gereserveerde_plaats);
	$totaalAantalKaarten += $bestelling_kaartsoorten[$kaartsoort['id']];
}

if($totaalprijs <= 0)
{
	$pagina=new Pagina('Bestelling niet verwerkt');
	$pagina->toonPrePagina();
	echo 'U heeft een bestelling van 0 kaarten geplaatst of het formulier is niet goed aangekomen.';
	$pagina->toonPostPagina();
	die();
}

$pagina = new Pagina('Uw bestelling is verwerkt');
$pagina->toonPrePagina();

echo 'Hartelijk dank voor uw bestelling. U ontvangt binnen enkele minuten een e-mail met een bevestiging van uw bestelling en betaalinformatie.';

$pagina->toonPostPagina();

//////////////////
// Stuur e-mail //
//////////////////
$extraheaders = 'From: "Vlissingse Oratorium Vereniging" <noreply@vlissingse-oratoriumvereniging.nl>
Content-Type: text/plain; charset="UTF-8"';

if ($bezorgen || ($concert['bezorgen_verplicht'] && !$ophalenDoorKoorlid))
{
	$opstuurtekst = 'naar uw adres verstuurd worden';
}
elseif ($concert['bezorgen_verplicht'] && $ophalenDoorKoorlid)
{
	$opstuurtekst = 'worden meegegeven aan ' . $naam_koorlid ;
}
else
{
	$opstuurtekst = 'voor u klaargelegd worden bij de ingang van de kerk';
}

$emailadres = geefPostVeilig('e-mailadres');
$achternaam = geefPostVeilig('achternaam');
$voorletters = geefPostVeilig('voorletters');
$straatnaam_en_huisnummer = geefPostVeilig('straatnaam_en_huisnummer');
$postcode = geefPostVeilig('postcode');
$woonplaats = geefPostVeilig('woonplaats');
$opmerkingen = geefPostVeilig('opmerkingen');

$bestellingsnummer=maakEen('INSERT INTO kaartverkoop_bestellingen
			(`concert_id`, 	`achternaam`, 	`voorletters`, 	`e-mailadres`, 	`straat_en_huisnummer`, 	`postcode`, `woonplaats`, 	`thuisbezorgen`, 		`gereserveerde_plaatsen`, 			`ophalen_door_koorlid`,	`naam_koorlid`,	`woont_in_buitenland`,	`opmerkingen`) VALUES
			(?, 			?, 				?, 				?, 				?, 							?, 			?, 				?, 						?, 									?,						?,				?,						?)', 
	array(	 $concert_id, 	$achternaam, 	$voorletters, 	$emailadres, 	$straatnaam_en_huisnummer, 	$postcode, 	$woonplaats, 	($bezorgen ? 1 : 0), 	($gereserveerde_plaatsen ? 1 : 0), 	$ophalenDoorKoorlid,	$naam_koorlid,	$buitenland,			$opmerkingen));
foreach($kaartsoorten as $kaartsoort)
{
	if($bestelling_kaartsoorten[$kaartsoort['id']]>0)
		maakEen('INSERT INTO kaartverkoop_bestellingen_kaartsoorten(`bestelling_id`, `kaartsoort_id`, `aantal`) VALUES(?, ?, ?)', array($bestellingsnummer, $kaartsoort['id'], $bestelling_kaartsoorten[$kaartsoort['id']]));
}

$voor_u_reserveerde_plaatsen = '';
if ($gereserveerde_plaatsen)
{
	$bezette_plaatsen_per_rij = array();

	$prep = $connectie->prepare('SELECT * FROM kaartverkoop_gereserveerde_plaatsen WHERE bestelling_id IN (SELECT id FROM kaartverkoop_bestellingen WHERE concert_id=?)');
	$prep->execute(array($concert_id));
	$bezette_plaatsen_rijen = $prep->fetchAll();

	foreach($bezette_plaatsen_rijen as $bezette_plaatsen)
	{
		for ($i = $bezette_plaatsen['eerste_stoel']; $i <= $bezette_plaatsen['laatste_stoel']; $i++)
		{
			$bezette_plaatsen_per_rij[$bezette_plaatsen['rij']][$i] = TRUE;
		}
	}	

	$plaatsGevonden = FALSE;
	$gereserveerde_rij = '';
	$eerste_stoel = 0;
	$laatste_stoel = 0;

//	for ($rij = 'A'; $rij <= 'P'; $rij++)
//	{
	$rij = 'A';
		$vrije_plaatsen_naast_elkaar = 0;
		for($stoel = 1; $stoel <= STOELEN_PER_RIJ; $stoel++)
		{
			if (isset($bezette_plaatsen_per_rij[$rij][$stoel]) && $bezette_plaatsen_per_rij[$rij][$stoel] == TRUE)
				$vrije_plaatsen_naast_elkaar = 0;
			else
				$vrije_plaatsen_naast_elkaar++;

			if ($vrije_plaatsen_naast_elkaar == $totaalAantalKaarten)
			{
				$plaatsGevonden = TRUE;
				$gereserveerde_rij = $rij;
				$eerste_stoel = $stoel - $totaalAantalKaarten + 1;
				$laatste_stoel = $stoel;
				//break 2;
				break;
			}
		}
//	}

	if ($plaatsGevonden)
	{
		maakEen('INSERT INTO kaartverkoop_gereserveerde_plaatsen(`bestelling_id`, `rij`, `eerste_stoel`, `laatste_stoel`) VALUES(?, ?, ?, ?)', array($bestellingsnummer, $gereserveerde_rij, $eerste_stoel, $laatste_stoel));
		$stoelen = range($eerste_stoel, $laatste_stoel);
		//$voor_u_reserveerde_plaatsen = "\r\n\r\nDe volgende plaatsen zijn voor u gereserveerd op rij $gereserveerde_rij: ";
		$voor_u_reserveerde_plaatsen = "\r\n\r\nDe volgende plaatsen zijn voor u gereserveerd: ";
		$voor_u_reserveerde_plaatsen .= implode(', ', $stoelen) . '.';
	}
	else		
	{
		$gereserveerde_plaatsen = FALSE;
		$totaalprijs -= $totaalAantalKaarten * $toeslag_gereserveerde_plaats;
		$voor_u_reserveerde_plaatsen = "\r\n\r\nEr waren helaas niet voldoende plaatsen om te reserveren. De gerekende toeslag voor gereserveerde kaarten is weer van het totaalbedrag afgetrokken.";
		maakEen('UPDATE kaartverkoop_bestellingen SET gereserveerde_plaatsen = 0 WHERE id=?', array($bestellingsnummer));
	}
}

$tekst='Hartelijk dank voor uw bestelling bij de Vlissingse Oratorium Vereniging.
Na betaling zullen uw kaarten '.$opstuurtekst.'.'.$voor_u_reserveerde_plaatsen.'

Gebruik bij het betalen de volgende gegevens:
   Rekeningnummer: NL06INGB0000545925 t.n.v. Vlissingse Oratorium Vereniging
   Bedrag: '.naarEuroPlain($totaalprijs).'
   Onder vermelding van: bestellingsnummer '.$bestellingsnummer.'



Hieronder volgt een overzicht van uw bestelling.

Bestellingsnummer: '.$bestellingsnummer.'

Kaartsoorten:
';
foreach($kaartsoorten as $kaartsoort)
{
	if($bestelling_kaartsoorten[$kaartsoort['id']]>0)
		$tekst.='   '.$kaartsoort['naam'] . ': '.$bestelling_kaartsoorten[$kaartsoort['id']].' à '.naarEuroPlain($kaartsoort['prijs'])."\n";
}
if (!$concert['bezorgen_verplicht'])
	$tekst.="\nKaarten bezorgen: ".($bezorgen ? 'Ja' : 'Nee');
$tekst.="\nGereserveerde plaatsen: ".($gereserveerde_plaatsen? 'Ja' : 'Nee')."\n";
$tekst.='Totaalbedrag: '.naarEuroPlain($totaalprijs).'

Achternaam: '.$achternaam.'
Voorletters: '.$voorletters."\n\n";

if($straatnaam_en_huisnummer)
	$tekst.='Straatnaam en huisnummer: '.$straatnaam_en_huisnummer."\n";

if($postcode)
	$tekst.='Postcode: '.$postcode."\n";

if($woonplaats)
	$tekst.='Woonplaats: '.$woonplaats."\n";

if($opmerkingen)
	$tekst.='Opmerkingen: '.$opmerkingen."\n";

mail($emailadres, 'Bestelling concertkaarten', $tekst, $extraheaders);

function checkFormulier($bezorgenVerplicht = FALSE, $ophalenDoorKoorlid = FALSE)
{
	$incorrecteVelden=array();
	if(strtoupper(geefPostVeilig('antispam')) !== 'VLISSINGEN')
		$incorrecteVelden[]='Antispam';

	if(strlen(geefPostVeilig('achternaam'))===0)
		$incorrecteVelden[]='Achternaam';

	if(strlen(geefPostVeilig('voorletters'))===0)
		$incorrecteVelden[]='Voorletters';

	if(strlen(geefPostVeilig('e-mailadres'))===0)
		$incorrecteVelden[]='E-mailadres';

	if((!$bezorgenVerplicht && geefPostVeilig('bezorgen')) || ($bezorgenVerplicht && !$ophalenDoorKoorlid))
	{
		if(strlen(geefPostVeilig('straatnaam_en_huisnummer'))===0)
			$incorrecteVelden[]='Straatnaam en huisnummer';

		if(strlen(geefPostVeilig('postcode'))===0)
			$incorrecteVelden[]='Postcode';

		if(strlen(geefPostVeilig('woonplaats'))===0)
			$incorrecteVelden[]='Woonplaats';
	}
	return $incorrecteVelden;
}
