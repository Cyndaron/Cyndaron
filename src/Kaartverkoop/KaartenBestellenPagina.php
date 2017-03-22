<?php
namespace Cyndaron\Kaartverkoop;

require_once __DIR__ . '/../../functies.db.php';
require_once __DIR__ . '/../../pagina.php';

class KaartenBestellenPagina extends \Pagina
{
    public function __construct()
    {
        $this->connectie = newPDO();
        $this->voegScriptToe('sys/js/kaartverkoop.js');

        $concert_id = geefGetVeilig('id');
        $prep = $this->connectie->prepare('SELECT * FROM kaartverkoop_concerten WHERE id=?');
        $prep->execute(array($concert_id));
        $concert_info = $prep->fetch();

        $concertnaam = $concert_info['naam'];

        parent::__construct('Kaarten bestellen: ' . $concertnaam);
        $this->toonPrePagina();

        if ($concert_info['open_voor_verkoop'] == FALSE)
        {
            if ($concert_info['beschrijving_indien_gesloten'])
                echo $concert_info['beschrijving_indien_gesloten'];
            else
                echo 'Voor dit concert kunt u kaarten kopen aan de kassa in de St. Jacobskerk voor aanvang van het concert. Bestellen via de website is voor dit concert niet meer mogelijk.';

            $this->toonPostPagina();
            die();
        }

        echo '<p>' . $concert_info['beschrijving'] . '</p>';
        ?>

        <h3>Vrije plaatsen en gereserveerde plaatsen</h3>
        <p>Alle plaatsen in het middenschip van de kerk verkopen wij met een stoelnummer; d.w.z. al deze plaatsen worden
            verkocht als gereserveerde plaats. De stoelnummers lopen van 1 t/m circa 300. Het is een doorlopende reeks,
            dus dit keer geen rijnummer. Aan het einde van een rij verspringt het stoelnummer naar de stoel daarachter.
            De nummers vormen een soort heen en weer gaande slinger door het hele middenschip heen. Het kan dus gebeuren
            dat u een paar kaarten koopt, waarbij de nummering verspringt naar de rij daarachter. Maar wel zo dat de
            stoelen dus direct bij elkaar staan.
            Vrije plaatsen zijn: de zijvakken en de balkons.</p>

        <br/>
        <form method="post" action="kaarten-verwerk-bestelling" class="form-horizontal" id="kaartenbestellen">
            <h3>Kaartsoorten:</h3>
            <input type="hidden" name="concert_id" value="<?php echo $concert_id; ?>"/>
            <table class="kaartverkoop table table-striped">
                <tr>
                    <th>Kaartsoort:</th>
                    <th>Prijs per stuk:</th>
                    <th>Aantal:</th>
                </tr>
                <?php
                $prep = $this->connectie->prepare('SELECT * FROM kaartverkoop_kaartsoorten WHERE concert_id=? ORDER BY prijs DESC');
                $prep->execute(array($concert_id));
                foreach ($prep->fetchAll() as $kaartsoort)
                {
                    printf('<tr>
				<td>%1$s</td>
				<td>%2$s</td>
				<td>
					<input class="aantalKaarten form-control form-control-inline" readonly="readonly" size="2" name="kaartsoort-%3$d" id="kaartsoort-%3$d" value="0"/>
					<button type="button" class="aantalKaarten btn btn-default aantalKaarten-increase" data-kaartsoort="%3$d"><span class="glyphicon glyphicon-plus"></span></button>
					<button type="button" class="aantalKaarten btn btn-default aantalKaarten-decrease" data-kaartsoort="%3$d"><span class="glyphicon glyphicon-minus"></span></button>
				</td>
		</tr>',
                        $kaartsoort['naam'], Util::naarEuro($kaartsoort['prijs']), $kaartsoort['id']);
                }
                ?>
            </table>
            <div <?= $concert_info['bezorgen_verplicht'] ? 'style="display:none"' : ''; ?>>
                <input id="bezorgen" name="bezorgen" type="checkbox" value="1" class="berekenTotaalprijsOpnieuw">
                <label for="bezorgen">
                    Bezorg mijn kaarten thuis (meerprijs van <?php echo Util::naarEuro($concert_info['verzendkosten']); ?> per kaart)
                </label>
            </div>

            <?php if ($concert_info['heeft_gereserveerde_plaatsen']): ?>
                <?php if ($concert_info['gereserveerde_plaatsen_uitverkocht']): ?>
                    <input id="gereserveerde_plaatsen" name="gereserveerde_plaatsen" style="display:none;" type="checkbox" value="1"/>
                    U kunt voor dit concert nog kaarten voor vrije plaatsen kopen. De gereserveerde plaatsen zijn inmiddels uitverkocht.
                <?php else: ?>
                    <input id="gereserveerde_plaatsen" class="berekenTotaalprijsOpnieuw" name="gereserveerde_plaatsen" type="checkbox" value="1"/>
                    <label for="gereserveerde_plaatsen">
                        Gereserveerde plaats met stoelnummer in het middenschip van de kerk (meerprijs van <?php echo Util::naarEuro($concert_info['toeslag_gereserveerde_plaats']); ?>per kaart)
                    </label>
                <?php endif; ?>
                <br/>
            <?php else: ?>
                <input id="gereserveerde_plaatsen" type="hidden" value="0">
            <?php endif; ?>
            <?php if ($concert_info['bezorgen_verplicht']): ?>
                <br>
                <h3>Bezorging</h3>
                <p>
                    Bij dit concert is het alleen mogelijk om uw kaarten te laten thuisbezorgen. Als u op Walcheren
                    woont is dit gratis. Woont u buiten Walcheren, dan kost het
                    thuisbezorgen <?= Util::naarEuro($concert_info['verzendkosten']); ?> per kaart.<br>Het is ook mogelijk
                    om uw kaarten te laten ophalen door een koorlid. Dit is gratis.
                    <a href="#" onclick="buitenland = true;">Woont u in het buitenland? Klik dan hier.</a>
                </p>
                <p>
                    Vul hieronder uw postcode in om de totaalprijs te laten berekenen.
                </p>

                <div class="form-group">
                    <label class="col-sm-2 control-label" for="postcode">Postcode (verplicht):</label>
                    <div class="col-sm-5"><input id="postcode" name="postcode" class="form-control form-control-inline" maxlength="7"/></div>
                </div>

                <div id="ophalen_door_koorlid_div" style="display:none;">
                    <input id="ophalen_door_koorlid" name="ophalen_door_koorlid" type="checkbox" value="1" class="berekenTotaalprijsOpnieuw">
                    <label for="ophalen_door_koorlid">Mijn kaarten laten ophalen door een koorlid</label>
                    <br>

                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="naam_koorlid">Naam koorlid:</label>
                        <div class="col-sm-5"><input id="naam_koorlid" name="naam_koorlid" type="text" class="form-control"/></div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="well"><b>Totaalprijs:</b> <span id="prijsvak">€&nbsp;0,00</span></div>

            <h3>Uw gegevens (verplicht):</h3>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="achternaam">Achternaam:</label>
                <div class="col-sm-5"><input id="achternaam" name="achternaam" class="form-control"/></div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="voorletters">Voorletters:</label>
                <div class="col-sm-5"><input id="voorletters" name="voorletters" class="form-control"/></div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="e-mailadres">E-mailadres:</label>
                <div class="col-sm-5"><input id="e-mailadres" name="e-mailadres" class="form-control"/></div>
            </div>


            <h3 id="adresgegevensKop">Uw adresgegevens (nodig als u de kaarten wilt laten bezorgen):</h3>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="straatnaam_en_huisnummer">Straatnaam en huisnummer:</label>
                <div class="col-sm-5"><input id="straatnaam_en_huisnummer" name="straatnaam_en_huisnummer"
                                             class="form-control"/></div>
            </div>

            <?php if (!$concert_info['bezorgen_verplicht']): ?>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="postcode">Postcode:</label>
                    <div class="col-sm-5"><input id="postcode" name="postcode" class="form-control"/></div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="woonplaats">Woonplaats:</label>
                <div class="col-sm-5"><input id="woonplaats" name="woonplaats" class="form-control"/></div>
            </div>


            <h3>Verzenden:</h3>

            <p>Als u nog opmerkingen heeft kunt u deze hier kwijt.</p>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="opmerkingen">Opmerkingen (niet verplicht):</label>
                <div class="col-sm-5"><textarea id="opmerkingen" name="opmerkingen" class="form-control" rows="4"></textarea></div>
            </div>

            <p>Om te voorkomen dat er spam wordt verstuurd met dit formulier<br/>wordt u verzocht in het onderstaande
                vak <span style="font-family:monospace;">Vlissingen</span> in te vullen.</p>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="antispam">Antispam:</label>
                <div class="col-sm-5"><input id="antispam" name="antispam" class="form-control"/></div>
            </div>

            <div class="col-sm-offset-2"><input id="verzendknop" class="btn btn-primary" type="submit" value="Bestellen"/></div>

            <input type="hidden" id="buitenland" name="buitenland" value="0"/>
        </form>
        <script type="text/javascript">

            var bezorgenVerplicht = <?=$concert_info['bezorgen_verplicht'];?>;
            var buitenland = false;

            function berekenTotaalprijs()
            {
                var totaalprijs = 0.0;

                if (buitenland)
                {
                    document.getElementById('ophalen_door_koorlid').checked = true;
                    document.getElementById('ophalen_door_koorlid').disabled = true;
                    document.getElementById('buitenland').value = 1;
                }

                if (bezorgenVerplicht)
                {
                    var postcode = document.getElementById('postcode').value;

                    if (postcode.length < 6 && !buitenland)
                    {
                        document.getElementById('prijsvak').innerHTML = "€&nbsp;-";
                        return;
                    }

                    var woontOpWalcheren = postcodeLigtInWalcheren(postcode);
                    var ophalenDoorKoorlid = document.getElementById('ophalen_door_koorlid').checked;

                    if (!woontOpWalcheren)
                    {
                        document.getElementById('ophalen_door_koorlid_div').style.display = "block";
                    }
                    else
                    {
                        document.getElementById('ophalen_door_koorlid_div').style.display = "none";
                    }

                    if (!woontOpWalcheren && !ophalenDoorKoorlid)
                    {
                        var bezorgen = true;
                    }
                    else
                    {
                        var bezorgen = false;
                        document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (niet verplicht):";
                    }
                }
                else
                {
                    var bezorgen = document.getElementById('bezorgen').checked;
                }

                if (bezorgen)
                {
                    var verzendkosten =<?php echo $concert_info['verzendkosten'];?>;
                    document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (verplicht):";
                }
                else
                {
                    var verzendkosten = 0.0;
                    if (!bezorgenVerplicht)
                        document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (niet verplicht):";
                }
                var toeslag_gereserveerde_plaats = 0.0;
                if (document.getElementById('gereserveerde_plaatsen').checked)
                {
                    toeslag_gereserveerde_plaats = <?php echo $concert_info['toeslag_gereserveerde_plaats'];?>;
                }

                if (!bezorgenVerplicht && bezorgen)
                {
                    document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (verplicht):";
                }
                else if (!bezorgenVerplicht && !bezorgen)
                {
                    document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (niet verplicht):";
                }
                else if (bezorgenVerplicht && !ophalenDoorKoorlid && !buitenland)
                {
                    document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (verplicht):";
                }
                else if (bezorgenVerplicht && (ophalenDoorKoorlid || buitenland))
                {
                    document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (niet verplicht):";
                }

                <?php
                $prep->execute(array($concert_id));
                foreach ($prep->fetchAll() as $kaartsoort)
                {
                    echo '	var aantal=document.getElementById("kaartsoort-' . $kaartsoort['id'] . "\").value;\n";
                    echo '	totaalprijs=totaalprijs+(' . $kaartsoort['prijs'] . "*aantal);\n";
                    echo '	totaalprijs=totaalprijs+(verzendkosten*aantal);' . "\n";
                    echo '	totaalprijs=totaalprijs+(toeslag_gereserveerde_plaats*aantal);' . "\n";
                }
                ?>
                totaalprijs_text = totaalprijs.toLocaleString("nl-NL", {
                    style: "currency",
                    currency: "EUR",
                    minimumFractionDigits: 2
                });
                document.getElementById('prijsvak').innerHTML = totaalprijs_text;
            }
        </script>
        <?php
        $this->toonPostPagina();
    }
}