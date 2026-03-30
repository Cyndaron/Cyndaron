/**
 * Copyright © 2009-2026 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more
 * details.
 */

'use strict';

const concertId = $('#concertId').val();
let tickettypes;
let defaultDeliveryCost;
let reservedSeatCharge;

$.ajax('/api/concert/getInfo/' + concertId, {
    dataType: "json"
}).done(function (json) {
    tickettypes = json.tickettypes;
    defaultDeliveryCost = json.defaultDeliveryCost;
    reservedSeatCharge = json.reservedSeatCharge;
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
    if (document.getElementById('antispam').value.toUpperCase() !== 'VLISSINGEN')
        return false;

    let priceHtml = document.getElementById('prijsvak').innerHTML;

    if (priceHtml === "€&nbsp;0,00" || priceHtml === "€&nbsp;-")
        return false;

    return true;
}

function checkNameAndEmail()
{
    let lastName = document.getElementById('lastName').value;
    let initials = document.getElementById('initials').value;
    let email = document.getElementById('email').value;

    return (lastName.length > 0 && initials.length > 0 && email.length > 0)
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
    let inputIsCorrect = checkForm();
    document.getElementById('verzendknop').disabled = !inputIsCorrect;
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
    let seatSurCharge = 0.0;
    const hasReservedElement = document.getElementById('hasReservedSeats-1');
    if (hasReservedElement && hasReservedElement.checked)
    {
        seatSurCharge = reservedSeatCharge;
    }

    let total = 0.0;
    let totalNumTickets = 0;
    tickettypes.forEach(function (item) {
        let amount = parseInt(document.getElementById('tickettype-' + item.id).value);
        let billedAmount = amount;
        if (item.discountPer5) {
            const numFree = Math.floor(amount / 5);
            billedAmount -= numFree;
        }
        totalNumTickets += amount;
        total = total + (item.price * billedAmount);
        total = total + (seatSurCharge * billedAmount);
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
