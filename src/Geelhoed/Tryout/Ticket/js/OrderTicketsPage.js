/**
 * Copyright © 2009-2025 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more
 * details.
 */

'use strict';

const eventId = $('#eventId').val();
let tickettypes;

$.ajax('/api/tryout-ticket/getInfo/' + eventId, {
    dataType: "json"
}).done(function (json) {
    tickettypes = json.tickettypes;
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

function checkGeneralFormFields()
{
    let priceHtml = document.getElementById('prijsvak').innerHTML;

    if (priceHtml === "€&nbsp;0,00" || priceHtml === "€&nbsp;-")
        return false;

    return true;
}

function checkNameAndEmail()
{
    let name = document.getElementById('name').value;
    let email = document.getElementById('email').value;

    return (name.length > 0 && email.length > 0)
}

function checkAddressInfo(delivery, deliveryByMember)
{
    if (addressIsRequired(delivery, deliveryByMember))
    {
        let street = document.getElementById('street').value;
        let postcode = document.getElementById('postcode').value;
        let city = document.getElementById('city').value;

        if (!(street.length > 0 && postcode.length > 0 && city.length > 0))
            return false;
    }

    return true;
}

function checkForm()
{
    if (!checkGeneralFormFields())
    {
        return false;
    }
    if (!checkNameAndEmail())
    {
        return false;
    }

    return true;
}

function blockFormOnInvalidInput()
{
    let invoerIsCorrect = checkForm();

    document.getElementById('verzendknop').disabled = !invoerIsCorrect;
}

function updateVisibleTotal(total)
{
    let totalFormatted = total.toLocaleString(
        'nl-NL',
        {style: 'currency', currency: 'EUR', minimumFractionDigits: 2});
    document.getElementById('prijsvak').innerHTML = totalFormatted;
}

function calculateTotal()
{
    let total = 0.0;
    let totalNumTickets = 0;
    tickettypes.forEach(function (item) {
        let count = parseInt(document.getElementById('tickettype-' + item.id).value);
        totalNumTickets += count;
        total = total + (item.price * count);
    });

    return total;
}

function updateForm()
{
    let total = calculateTotal();

    updateVisibleTotal(total);
}

$('.numTickets-increase').on('click', function () {
    increase('tickettype-' + $(this).attr('data-kaartsoort'));
});
$('.numTickets-decrease').on('click', function () {
    decrease('tickettype-' + $(this).attr('data-kaartsoort'));
});
$('.recalculateTotal').on('click', function () {
    updateForm();
});

setInterval(blockFormOnInvalidInput, 1000);
setInterval(updateForm, 1000);
