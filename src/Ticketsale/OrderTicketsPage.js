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
let addressIsAbroad = false;
let defaultDeliveryCost;
let reservedSeatCharge;

$.ajax('/api/concert/getInfo/' + concertId, {}).done(function (json)
{
    let data = JSON.parse(json);
    tickettypes = data.tickettypes;
    forcedDelivery = data.forcedDelivery;
    defaultDeliveryCost = data.defaultDeliveryCost;
    reservedSeatCharge = data.reservedSeatCharge;
});

function increase(vak)
{
    let element = document.getElementById(vak);
    if (element.value < 100)
    {
        element.value++;
        updateForm()
    }
}
function decrease(vak)
{
    let element = document.getElementById(vak);
    if (element.value > 0)
    {
        element.value--;
        updateForm()
    }
}

function checkForm()
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

    if (addressIsAbroad && document.getElementById('deliveryMemberName').value.length < 2)
        return false;

    return true;
}

function blockFormOnInvalidInput()
{
    let invoerIsCorrect = checkForm();

    document.getElementById('verzendknop').disabled = !invoerIsCorrect;
}

function postcodeQualifiesForFreeDelivery(postcode)
{
    if (addressIsAbroad === true)
        return false;

    postcode = parseInt(postcode);

    if (postcode >= 4330 && postcode <= 4399)
        return true;
    else
        return false;
}

function updateAddressRequirement(delivery, deliveryByMember)
{
    if (delivery)
    {
        document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (verplicht):";
    }
    if (!forcedDelivery && delivery)
    {
        document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (verplicht):";
    }
    else if (!forcedDelivery && !delivery)
    {
        document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (niet verplicht):";
    }
    else if (forcedDelivery && !deliveryByMember && !addressIsAbroad)
    {
        document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (verplicht):";
    }
    else if (forcedDelivery && (deliveryByMember || addressIsAbroad))
    {
        document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (niet verplicht):";
    }
}

function updateVisibleTotal(total)
{
    let totalFormatted = total.toLocaleString('nl-NL', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2
    });
    document.getElementById('prijsvak').innerHTML = totalFormatted;
}

function updateAddressIsAbroadFields()
{
    if (addressIsAbroad)
    {
        document.getElementById('deliveryByMember').checked = true;
        document.getElementById('deliveryByMember').disabled = true;

        $('.postcode-related').css({display: 'none'});
    }
    else
    {
        document.getElementById('deliveryByMember').disabled = false;
        $('.postcode-related').css({display: 'flex'});
    }
}

function calculateTotal(delivery)
{
    let deliveryCost = 0.0;
    if (delivery)
    {
        deliveryCost = defaultDeliveryCost;
    }

    let seatSurCharge = 0.0;
    if (document.getElementById('hasReservedSeats').checked)
    {
        seatSurCharge = reservedSeatCharge;
    }

    let total = 0.0;
    tickettypes.forEach(function (item) {
        let aantal = document.getElementById('tickettype-' + item.id).value;
        total = total + (item.price * aantal);
        total = total + (deliveryCost * aantal);
        total = total + (seatSurCharge * aantal);
    });
    return total;
}

function updateForm()
{
    updateAddressIsAbroadFields();

    let delivery = false;
    let deliveryByMember = false;
    if (forcedDelivery) {
        let postcode = document.getElementById('postcode').value;

        if (postcode.length < 6 && !addressIsAbroad) {
            document.getElementById('prijsvak').innerHTML = "€&nbsp;-";
            return;
        }

        let qualifiesForFreeDelivery = postcodeQualifiesForFreeDelivery(postcode);
        deliveryByMember = document.getElementById('deliveryByMember').checked;

        if (!qualifiesForFreeDelivery) {
            document.getElementById('deliveryByMember_div').style.display = "block";
        }
        else {
            document.getElementById('deliveryByMember_div').style.display = "none";
        }

        if (!qualifiesForFreeDelivery && !deliveryByMember) {
            delivery = true;
        }
        else {
            delivery = false;
            document.getElementById('adresgegevensKop').innerHTML = "Uw adresgegevens (niet verplicht):";
        }
    }
    else
    {
        delivery = document.getElementById('bezorgen').checked;
    }

    let total = calculateTotal(delivery);

    updateVisibleTotal(total);
    updateAddressRequirement(delivery, deliveryByMember);
}

$('.numTickets-increase').on('click', function() { increase('tickettype-' + $(this).attr('data-kaartsoort')); });
$('.numTickets-decrease').on('click', function() { decrease('tickettype-' + $(this).attr('data-kaartsoort')); });
$('.recalculateTotal').on('click', function() { updateForm(); });
$('input[type=radio][name=country]').on('change', function()
{
    if (this.value === 'abroad')
    {
        addressIsAbroad = true;
    }
    else
    {
        addressIsAbroad = false;
        document.getElementById('deliveryByMember').checked = false;

    }
});

setInterval(blockFormOnInvalidInput, 1000);
setInterval(updateForm, 1000);
