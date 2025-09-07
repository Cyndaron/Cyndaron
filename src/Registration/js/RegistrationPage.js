/**
 * Copyright © 2009-2025 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */

'use strict';

const eventId = $('#eventId').val();
let tickettypes = [];
let registrationCost0 = NaN;
let registrationCost1 = NaN;
let registrationCost2 = NaN;
let registrationCost3 = NaN;
let lunchCost = NaN;

$.ajax('/api/event/getInfo/' + eventId, {}).done(function (data)
{
    tickettypes = data.tickettypes;
    registrationCost0 = parseFloat(data.registrationCost0);
    registrationCost1 = parseFloat(data.registrationCost1);
    registrationCost2 = parseFloat(data.registrationCost2);
    registrationCost3 = parseFloat(data.registrationCost3);
    lunchCost = parseFloat(data.lunchCost);
});

function increase(vak)
{
    let element = document.getElementById(vak);
    if (element.value < 100)
    {
        element.value++;
        calculateTotal()
    }
}
function decrease(vak)
{
    let element = document.getElementById(vak);
    if (element.value > 0)
    {
        element.value--;
        calculateTotal()
    }
}

function formInputIsValid()
{
    let requiredFields = $('#kaartenbestellen input,#kaartenbestellen textarea,#kaartenbestellen select').filter('[required]:visible');
    for (let i = 0; i < requiredFields.length; i++)
    {
        if (!requiredFields[i].checkValidity())
        {
            return false;
        }
    }
    return true;
}

function blockFormOnInvalidInput()
{
    document.getElementById('verzendknop').disabled = !formInputIsValid();
}

function calculateTotal()
{
    let total = 0;
    let regGroup = 0;
    let input = $('[name=registrationGroup]:checked');
    if (input.length > 0)
    {
        regGroup = parseInt(input.val());
    }
    else
    {
        let input = $('[name=registrationGroup]').first();
        if (input.length > 0)
        {
            regGroup = parseInt(input.val());
        }
    }

    if (regGroup === 3)
        total += registrationCost3;
    else if (regGroup === 2)
        total += registrationCost2;
    else if (regGroup === 1)
        total += registrationCost1;
    else
        total += registrationCost0;

    if ($('#lunch').is(':checked'))
        total += lunchCost;

    tickettypes.forEach(function(item)
    {
        let aantal = document.getElementById('tickettype-' + item.id).value;
        if (parseInt(item.discountPer5) === 1)
            aantal -= Math.floor(aantal / 5);
        total = total + (item.price * aantal);
    });

    let totalFormatted = formatEuro(total);
    document.getElementById('prijsvak').innerHTML = totalFormatted;
}

$('.numTickets-increase').on('click', function() { increase('tickettype-' + $(this).attr('data-kaartsoort')); });
$('.numTickets-decrease').on('click', function() { decrease('tickettype-' + $(this).attr('data-kaartsoort')); });
$('.recalculateTotal').on('click', function() { calculateTotal(); });
$('#kleinkoor').on('change', function()
{
    let explanationWrapper = $('#kleinkoorExplanationWrapper');
    if ($(this).is(':checked'))
        explanationWrapper.css('display', 'block');
    else
        explanationWrapper.css('display', 'none');
});
$('#lunch').on('change', function()
{
    let lunchTypeWrapper = $('#lunchTypeWrapper');
    if ($(this).is(':checked'))
        lunchTypeWrapper.css('display', 'block');
    else
        lunchTypeWrapper.css('display', 'none');
});

setInterval(blockFormOnInvalidInput, 1000);
setInterval(calculateTotal, 1000);

