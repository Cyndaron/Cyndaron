<?php
require_once('functies.db.php');
require_once('pagina.php');

function naarEuro($bedrag)
{
	return '&euro;&nbsp;' . number_format($bedrag, 2, ',', '.');
}
$connectie = newPDO();

$concert_id = intval(htmlentities($_GET['id'], NULL, 'UTF-8'));
$prep = $connectie->prepare('SELECT * FROM kaartverkoop_concerten WHERE id=?');
$prep->execute(array($concert_id));
$concert_info = $prep->fetchAll();

$concertnaam=$concert_info[0]['naam'];

$pagina=new Pagina('Kaarten bestellen: '.$concertnaam);
$pagina->toonPrePagina();

if($concert_info[0]['open_voor_verkoop'] == FALSE)
{
	if ($concert_info[0]['beschrijving_indien_gesloten'])
		echo $concert_info[0]['beschrijving_indien_gesloten'];
	else
		echo 'Voor dit concert kunt u kaarten kopen aan de kassa in de St. Jacobskerk voor aanvang van het concert. Bestellen via de website is voor dit concert niet meer mogelijk.';

	$pagina->toonPostPagina();
	die();
}

echo '<p>' . $concert_info[0]['beschrijving'] . '</p>';
?>

<h3>Vrije plaatsen en gereserveerde plaatsen</h3>
<p>Alle plaatsen in het middenschip van de kerk verkopen wij met een stoelnummer; d.w.z. al deze plaatsen worden verkocht als gereserveerde plaats. De stoelnummers lopen van 1 t/m circa 300. Het is een doorlopende reeks, dus dit keer geen rijnummer. Aan het einde van een rij verspringt het stoelnummer naar de stoel daarachter. De nummers vormen een soort heen en weer gaande slinger door het hele middenschip heen. Het kan dus gebeuren dat u een paar kaarten koopt, waarbij de nummering  verspringt naar de rij daarachter. Maar wel zo dat de stoelen dus direct bij elkaar staan.
Vrije plaatsen zijn: de zijvakken en de balkons.</p>

<br />
<form method="post" action="verwerk-kaarten-bestellen">
<h3>Kaartsoorten:</h3>
<input type="hidden" name="concert_id" value="<?php echo $concert_id;?>"/>
<table class="kaartverkoop">
<tr><th>Kaartsoort:</th><th>Prijs / stuk:</th><th>Aantal:</th></tr>
<?php
$prep = $connectie->prepare('SELECT * FROM kaartverkoop_kaartsoorten WHERE concert_id=? ORDER BY prijs DESC');
$prep->execute(array($concert_id));
foreach($prep->fetchAll() as $kaartsoort)
{
	printf('<tr>
				<td>%1$s</td>
				<td>%2$s</td>
				<td>
					<input class="aantalKaarten" readonly="readonly" size="2" name="kaartsoort-%3$d" id="kaartsoort-%3$d" value="0"/>
					<button type="button" onclick="javascript:increase(\'kaartsoort-%3$d\');">+</button>
					<button type="button" onclick="javascript:decrease(\'kaartsoort-%3$d\');">−</button>
				</td>
		</tr>',
	$kaartsoort['naam'], naarEuro($kaartsoort['prijs']), $kaartsoort['id']);
}
?>
</table>
<div <?=$concert_info[0]['bezorgen_verplicht'] ? 'style="display:none"' : '';?>><input id="bezorgen" name="bezorgen" onclick="javascript:berekenTotaalprijs();" type="checkbox" value="1"/><label for="bezorgen">Bezorg mijn kaarten thuis (meerprijs van <?php echo naarEuro($concert_info[0]['verzendkosten']);?> per kaart)</label></div>

<?php if ($concert_info[0]['heeft_gereserveerde_plaatsen']): ?>
    <?php if ($concert_info[0]['gereserveerde_plaatsen_uitverkocht']): ?>
        <input id="gereserveerde_plaatsen" name="gereserveerde_plaatsen" style="display:none;" type="checkbox" value="1" />
        U kunt voor dit concert nog kaarten voor vrije plaatsen kopen. De gereserveerde plaatsen zijn inmiddels uitverkocht.
    <?php else: ?>
        <input id="gereserveerde_plaatsen" name="gereserveerde_plaatsen" onclick="javascript:berekenTotaalprijs();" type="checkbox" value="1" /><label for="gereserveerde_plaatsen">Gereserveerde plaats met stoelnummer in het middenschip van de kerk (meerprijs van <?php echo naarEuro($concert_info[0]['toeslag_gereserveerde_plaats']);?> per kaart)</label>
    <?php endif; ?>
    <br />
<?php else: ?>
<input id="gereserveerde_plaatsen" type="hidden" value="0">
<?php endif; ?>
<?php if ($concert_info[0]['bezorgen_verplicht']): ?>
<br>
	<h3>Bezorging</h3>
	<p>
		Bij dit concert is het alleen mogelijk om uw kaarten te laten thuisbezorgen. Als u op Walcheren woont is dit gratis. Woont u buiten Walcheren, dan kost het thuisbezorgen <?=naarEuro($concert_info[0]['verzendkosten']);?> per kaart.<br>Het is ook mogelijk om uw kaarten te laten ophalen door een koorlid. Dit is gratis. <a href="#" onclick="buitenland = true;">Woont u in het buitenland? Klik dan hier.</a>
	</p>
	<p>
		Vul hieronder uw postcode in om de totaalprijs te laten berekenen.<br>
<table>
	<tr><td>Postcode <b>(verplicht)</b>:</td><td><input id="postcode" name="postcode"/></td></tr>
	<tr><td colspan="100%">
		<div id="ophalen_door_koorlid_div" style="display:none;">
			<input id="ophalen_door_koorlid" name="ophalen_door_koorlid" onclick="javascript:berekenTotaalprijs();" type="checkbox" value="1" /><label for="ophalen_door_koorlid">Mijn kaarten laten ophalen door een koorlid</label><br>
			&nbsp; &nbsp; &nbsp; <label for="naam_koorlid">Naam koorlid: </label><input id="naam_koorlid" name="naam_koorlid" type="text" />
		</div>
	</td></tr>
<?php endif; ?>
<tr><td colspan="100%">
	<p><b>Totaalprijs:</b> <span id="prijsvak">€&nbsp;0,00</span></p>
</td></tr>
<tr><td colspan="100%"><h3>Uw gegevens (verplicht):</h3></td></tr>
<tr><td>Achternaam:</td><td><input id="achternaam" name="achternaam"/></td></tr>
<tr><td>Voorletters:</td><td><input id="voorletters" name="voorletters"/></td></tr>
<tr><td>E-mailadres:</td><td><input id="e-mailadres" name="e-mailadres"/></td></tr>
<tr><td colspan="100%"><h3 id="adresgegevensKop">Uw adresgegevens (nodig als u de kaarten wilt laten bezorgen):</h3></td></tr>
<tr><td>Straatnaam en huisnummer:</td><td><input id="straatnaam_en_huisnummer" name="straatnaam_en_huisnummer"/></td></tr>
<?php if (!$concert_info[0]['bezorgen_verplicht']): ?>
	<tr><td>Postcode:</td><td><input id="postcode" name="postcode"/></td></tr>
<?php endif; ?>
<tr><td>Woonplaats:</td><td><input id="woonplaats" name="woonplaats"/></td></tr>
<tr><td colspan="100%"><h3>Opmerkingen (niet verplicht):</h3></td></tr>
<tr><td colspan="100%"><textarea id="opmerkingen" name="opmerkingen" cols="50" rows="4"></textarea></td></tr>
<tr><td colspan="100%"><h3>Verzenden:</h3></td></tr>
<tr><td colspan="100%" style="padding-bottom:15px;">Om te voorkomen dat er spam wordt verstuurd met dit formulier<br />wordt u verzocht in het onderstaande vak <span style="font-family:monospace;">Vlissingen</span> in te vullen.</td></tr>
<tr><td>Antispam:</td><td><input id="antispam" name="antispam"/></td></tr>
<tr><td colspan="100%"><input id="verzendknop" type="submit" value="Bestellen" /></td></tr>
</table>
<input type="hidden" id="buitenland" name="buitenland" value="0"/>
</form>
<script type="text/javascript">

var bezorgenVerplicht = <?=$concert_info[0]['bezorgen_verplicht'];?>;
var buitenland = false;

setInterval(blokkeerFormulierBijOngeldigeInvoer, 1000);

function increase(vak)
{
	var element=document.getElementById(vak);
	if(element.value<100)
	{
		element.value++;
		berekenTotaalprijs()
	}
}
function decrease(vak)
{
	var element=document.getElementById(vak);
	if(element.value>0)
	{
		element.value--;
		berekenTotaalprijs()
	}
}
function berekenTotaalprijs()
{
	var totaalprijs=0.0;

	if (buitenland)
	{
		document.getElementById('ophalen_door_koorlid').checked = true;
		document.getElementById('ophalen_door_koorlid').disabled = true;
		document.getElementById('buitenland').value = 1;
	}

	if (bezorgenVerplicht)
	{
		var postcode=document.getElementById('postcode').value;

		if (postcode.length < 6 && !buitenland)
		{
			document.getElementById('prijsvak').innerHTML = "€&nbsp;-";
			return;
		}

		var woontOpWalcheren = postcodeLigtInWalcheren(postcode);
		var ophalenDoorKoorlid = document.getElementById('ophalen_door_koorlid').checked;

		if (!woontOpWalcheren)
		{
			document.getElementById('ophalen_door_koorlid_div').style.display="block";
		}
		else
		{
			document.getElementById('ophalen_door_koorlid_div').style.display="none";
		}

		if (!woontOpWalcheren && !ophalenDoorKoorlid)
		{
			var bezorgen = true;
		}
		else
		{
			var bezorgen = false;
			document.getElementById('adresgegevensKop').innerHTML="Uw adresgegevens (niet verplicht):";
		}
	}
	else
	{
		var bezorgen = document.getElementById('bezorgen').checked;
	}

	if (bezorgen)
	{
		var verzendkosten=<?php echo $concert_info[0]['verzendkosten'];?>;
		document.getElementById('adresgegevensKop').innerHTML="Uw adresgegevens (verplicht):";
	}
	else
	{
		var verzendkosten=0.0;
		if (!bezorgenVerplicht)
			document.getElementById('adresgegevensKop').innerHTML="Uw adresgegevens (niet verplicht):";
	}
	var toeslag_gereserveerde_plaats = 0.0;
	if (document.getElementById('gereserveerde_plaatsen').checked)
	{
		toeslag_gereserveerde_plaats = <?php echo $concert_info[0]['toeslag_gereserveerde_plaats'];?>;
	}

	if(!bezorgenVerplicht && bezorgen)
	{
			document.getElementById('adresgegevensKop').innerHTML="Uw adresgegevens (verplicht):";
	}
	else if (!bezorgenVerplicht && !bezorgen)
	{
			document.getElementById('adresgegevensKop').innerHTML="Uw adresgegevens (niet verplicht):";
	}
	else if (bezorgenVerplicht && !ophalenDoorKoorlid && !buitenland)
	{
			document.getElementById('adresgegevensKop').innerHTML="Uw adresgegevens (verplicht):";
	}
	else if (bezorgenVerplicht && (ophalenDoorKoorlid || buitenland))
	{
			document.getElementById('adresgegevensKop').innerHTML="Uw adresgegevens (niet verplicht):";
	}

	<?php
	$prep->execute(array($concert_id));
	foreach($prep->fetchAll() as $kaartsoort)
	{
		echo '	var aantal=document.getElementById("kaartsoort-'.$kaartsoort['id']."\").value;\n";
		echo '	totaalprijs=totaalprijs+('.$kaartsoort['prijs']."*aantal);\n";
		echo '	totaalprijs=totaalprijs+(verzendkosten*aantal);'."\n";
		echo '	totaalprijs=totaalprijs+(toeslag_gereserveerde_plaats*aantal);'."\n";
	}
	?>
	totaalprijs_text=totaalprijs.toLocaleString("nl-NL", {style: "currency", currency: "EUR", minimumFractionDigits: 2});
	document.getElementById('prijsvak').innerHTML=totaalprijs_text;
}

function checkFormulier()
{
	if(document.getElementById('antispam').value.toUpperCase() !== 'VLISSINGEN')
		return false;

	if(document.getElementById('prijsvak').innerHTML=="€&nbsp;0,00")
		return false;

	if(document.getElementById('prijsvak').innerHTML=="€&nbsp;-")
		return false;

	var achternaam=document.getElementById('achternaam').value;
	var voorletters=document.getElementById('voorletters').value;
	var emailadres=document.getElementById('e-mailadres').value;
	var ophalenDoorKoorlid = document.getElementById('ophalen_door_koorlid').checked;

	if(!(achternaam.length>0 && voorletters.length>0 && emailadres.length>0))
		return false;

	if (document.getElementById('bezorgen').checked || (bezorgenVerplicht && !ophalenDoorKoorlid))
	{
		var straatnaam_en_huisnummer=document.getElementById('straatnaam_en_huisnummer').value;
		var postcode=document.getElementById('postcode').value;
		var woonplaats=document.getElementById('woonplaats').value;

		if(!(straatnaam_en_huisnummer.length>0 && postcode.length>0 && woonplaats.length>0))
			return false;
	}

	if (ophalenDoorKoorlid && document.getElementById('naam_koorlid').value.length < 2)
		return false;

	if (buitenland && document.getElementById('naam_koorlid').value.length < 2)
		return false;

	return true;
}

function blokkeerFormulierBijOngeldigeInvoer()
{
	var invoerIsCorrect=checkFormulier();

	document.getElementById('verzendknop').disabled=!invoerIsCorrect;
}

function postcodeLigtInWalcheren(postcode)
{
	if (buitenland == true)
		return false;

	postcode = parseInt(postcode);

	if (postcode >= 4330 && postcode <= 4399)
		return true;
	else
		return false;
}

setInterval(berekenTotaalprijs, 1000);

</script>
<?php
$pagina->toonPostPagina();
