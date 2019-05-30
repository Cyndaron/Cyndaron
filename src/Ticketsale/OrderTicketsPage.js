/*
 * Copyright © 2009-2019, Michael Steenbeek
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

'use strict';

const concertId = $('#concertId').val();
let tickettypes;
let forcedDelivery;
let buitenland = false;
let standaardVerzendkosten;
let toeslagGereserveerdePlaats;

$.ajax('/concert/getInfo/' + concertId, {}).done(function (json)
{
    let data = JSON.parse(json);
    tickettypes = data.tickettypes;
    forcedDelivery = data.forcedDelivery;
    standaardVerzendkosten = data.standaardVerzendkosten;
    toeslagGereserveerdePlaats = data.toeslagGereserveerdePlaats;
});

function increase(vak)
{
    let element = document.getElementById(vak);
    if (element.value < 100)
    {
        element.value++;
        berekenTotaalprijs()
    }
}
function decrease(vak)
{
    let element = document.getElementById(vak);
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

    let lastName = document.getElementById('lastName').value;
    let initials = document.getElementById('initials').value;
    let email = document.getElementById('email').value;
    let deliveryByMember = document.getElementById('deliveryByMember').checked;

    if(!(lastName.length > 0 && initials.length > 0 && email.length > 0))
        return false;

    if (document.getElementById('bezorgen').checked || (forcedDelivery && !deliveryByMember))
    {
        let street = document.getElementById('street').value;
        let postcode = document.getElementById('postcode').value;
        let city = document.getElementById('city').value;

        if(!(street.length > 0 && postcode.length > 0 && city.length > 0))
            return false;
    }

    if (deliveryByMember && document.getElementById('deliveryMemberName').value.length < 2)
        return false;

    if (buitenland && document.getElementById('deliveryMemberName').value.length < 2)
        return false;

    return true;
}

function blokkeerFormulierBijOngeldigeInvoer()
{
    let invoerIsCorrect = checkFormulier();

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
    let totaalprijs = 0.0;
    let bezorgen = false;
    let verzendkosten = 0.0;

    if (buitenland)
    {
        document.getElementById('deliveryByMember').checked = true;
        document.getElementById('deliveryByMember').disabled = true;

        $('.postcode-gerelateerd').css({display: 'none'});
    }
    else
    {
        document.getElementById('deliveryByMember').disabled = false;
        $('.postcode-gerelateerd').css({display: 'flex'});
    }

    let deliveryByMember = false;
    if (forcedDelivery) {
        let postcode = document.getElementById('postcode').value;

        if (postcode.length < 6 && !buitenland) {
            document.getElementById('prijsvak').innerHTML = "€&nbsp;-";
            return;
        }

        let woontOpWalcheren = postcodeLigtInWalcheren(postcode);
        deliveryByMember = document.getElementById('deliveryByMember').checked;

        if (!woontOpWalcheren) {
            document.getElementById('deliveryByMember_div').style.display = "block";
        }
        else {
            document.getElementById('deliveryByMember_div').style.display = "none";
        }

        if (!woontOpWalcheren && !deliveryByMember) {
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
        if (!forcedDelivery)
            document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (niet verplicht):";
    }
    let toeslag_gereserveerde_plaats = 0.0;
    if (document.getElementById('hasReservedSeats').checked) {
        toeslag_gereserveerde_plaats = toeslagGereserveerdePlaats;
    }

    if (!forcedDelivery && bezorgen) {
        document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (verplicht):";
    }
    else if (!forcedDelivery && !bezorgen) {
        document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (niet verplicht):";
    }
    else if (forcedDelivery && !deliveryByMember && !buitenland) {
        document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (verplicht):";
    }
    else if (forcedDelivery && (deliveryByMember || buitenland)) {
        document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (niet verplicht):";
    }

    tickettypes.forEach(function(item) {
        let aantal = document.getElementById('kaartsoort-' + item.id).value;
        totaalprijs = totaalprijs + (item.price * aantal);
        totaalprijs = totaalprijs + (verzendkosten * aantal);
        totaalprijs = totaalprijs + (toeslag_gereserveerde_plaats * aantal);
    });

    let totaalprijs_text = totaalprijs.toLocaleString("nl-NL", {
        style: "currency",
        currency: "EUR",
        minimumFractionDigits: 2
    });
    document.getElementById('prijsvak').innerHTML = totaalprijs_text;
}

$('.aantalKaarten-increase').on('click', function() { increase('kaartsoort-' + $(this).attr('data-kaartsoort')); });
$('.aantalKaarten-decrease').on('click', function() { decrease('kaartsoort-' + $(this).attr('data-kaartsoort')); });
$('.berekenTotaalprijsOpnieuw').on('click', function() { berekenTotaalprijs(); });
$('input[type=radio][name=land]').on('change', function()
{
    if (this.value === 'buitenland')
    {
        buitenland = true;
    }
    else
    {
        buitenland = false;
        document.getElementById('deliveryByMember').checked = false;

    }
});

setInterval(blokkeerFormulierBijOngeldigeInvoer, 1000);
setInterval(berekenTotaalprijs, 1000);

