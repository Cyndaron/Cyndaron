/*
 * Copyright © 2009-2017, Michael Steenbeek
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

"use strict";

var concertId = $('#concertId').val();
var kaartsoorten;
var bezorgenVerplicht;
var buitenland = false;
var standaardVerzendkosten;
var toeslagGereserveerdePlaats;

$.post('kaarten-ajax-endpoint', {actie: 'geefKaartsoorten', concertId: concertId}).done(function (json)
{
    var data = JSON.parse(json);
    kaartsoorten = data.kaartsoorten;
    bezorgenVerplicht = data.bezorgenVerplicht;
    standaardVerzendkosten = data.standaardVerzendkosten;
    toeslagGereserveerdePlaats = data.toeslagGereserveerdePlaats;
});

function increase(vak)
{
    var element = document.getElementById(vak);
    if (element.value < 100)
    {
        element.value++;
        berekenTotaalprijs()
    }
}
function decrease(vak)
{
    var element = document.getElementById(vak);
    if (element.value > 0)
    {
        element.value--;
        berekenTotaalprijs()
    }
}

function checkFormulier()
{
    if (document.getElementById('antispam').value.toUpperCase() !== 'VLISSINGEN')
        return false;

    if (document.getElementById('prijsvak').innerHTML === "€&nbsp;0,00")
        return false;

    if (document.getElementById('prijsvak').innerHTML === "€&nbsp;-")
        return false;

    var achternaam = document.getElementById('achternaam').value;
    var voorletters = document.getElementById('voorletters').value;
    var emailadres = document.getElementById('e-mailadres').value;
    var ophalenDoorKoorlid = document.getElementById('ophalen_door_koorlid').checked;

    if(!(achternaam.length > 0 && voorletters.length > 0 && emailadres.length > 0))
        return false;

    if (document.getElementById('bezorgen').checked || (bezorgenVerplicht && !ophalenDoorKoorlid))
    {
        var straatnaam_en_huisnummer = document.getElementById('straatnaam_en_huisnummer').value;
        var postcode = document.getElementById('postcode').value;
        var woonplaats = document.getElementById('woonplaats').value;

        if(!(straatnaam_en_huisnummer.length > 0 && postcode.length > 0 && woonplaats.length > 0))
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
    var invoerIsCorrect = checkFormulier();

    document.getElementById('verzendknop').disabled = !invoerIsCorrect;
}

function postcodeLigtInWalcheren(postcode)
{
    if (buitenland === true)
        return false;

    postcode = parseInt(postcode);

    if (postcode >= 4330 && postcode <= 4399)
        return true;
    else
        return false;
}

function berekenTotaalprijs()
{
    var totaalprijs = 0.0;
    var bezorgen = false;
    var verzendkosten = 0.0;

    if (buitenland) {
        document.getElementById('ophalen_door_koorlid').checked = true;
        document.getElementById('ophalen_door_koorlid').disabled = true;
        document.getElementById('buitenland').value = 1;
    }

    if (bezorgenVerplicht) {
        var postcode = document.getElementById('postcode').value;

        if (postcode.length < 6 && !buitenland) {
            document.getElementById('prijsvak').innerHTML = "€&nbsp;-";
            return;
        }

        var woontOpWalcheren = postcodeLigtInWalcheren(postcode);
        var ophalenDoorKoorlid = document.getElementById('ophalen_door_koorlid').checked;

        if (!woontOpWalcheren) {
            document.getElementById('ophalen_door_koorlid_div').style.display = "block";
        }
        else {
            document.getElementById('ophalen_door_koorlid_div').style.display = "none";
        }

        if (!woontOpWalcheren && !ophalenDoorKoorlid) {
            bezorgen = true;
        }
        else {
            bezorgen = false;
            document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (niet verplicht):";
        }
    }
    else {
        bezorgen = document.getElementById('bezorgen').checked;
    }

    if (bezorgen) {
        verzendkosten = standaardVerzendkosten;
        document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (verplicht):";
    }
    else {
        verzendkosten = 0.0;
        if (!bezorgenVerplicht)
            document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (niet verplicht):";
    }
    var toeslag_gereserveerde_plaats = 0.0;
    if (document.getElementById('gereserveerde_plaatsen').checked) {
        toeslag_gereserveerde_plaats = toeslagGereserveerdePlaats;
    }

    if (!bezorgenVerplicht && bezorgen) {
        document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (verplicht):";
    }
    else if (!bezorgenVerplicht && !bezorgen) {
        document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (niet verplicht):";
    }
    else if (bezorgenVerplicht && !ophalenDoorKoorlid && !buitenland) {
        document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (verplicht):";
    }
    else if (bezorgenVerplicht && (ophalenDoorKoorlid || buitenland)) {
        document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (niet verplicht):";
    }

    kaartsoorten.forEach(function(item) {
        var aantal = document.getElementById('kaartsoort-' + item.id).value;
        totaalprijs = totaalprijs + (item.prijs * aantal);
        totaalprijs = totaalprijs + (verzendkosten * aantal);
        totaalprijs = totaalprijs + (toeslag_gereserveerde_plaats * aantal);
    });

    var totaalprijs_text = totaalprijs.toLocaleString("nl-NL", {
        style: "currency",
        currency: "EUR",
        minimumFractionDigits: 2
    });
    document.getElementById('prijsvak').innerHTML = totaalprijs_text;
}

$('.aantalKaarten-increase').on('click', function() { increase('kaartsoort-' + $(this).attr('data-kaartsoort')); });
$('.aantalKaarten-decrease').on('click', function() { decrease('kaartsoort-' + $(this).attr('data-kaartsoort')); });
$('.berekenTotaalprijsOpnieuw').on('click', function() { berekenTotaalprijs(); });
$('#buitenland').on('click', function() { buitenland = true; });

setInterval(blokkeerFormulierBijOngeldigeInvoer, 1000);
setInterval(berekenTotaalprijs, 1000);

