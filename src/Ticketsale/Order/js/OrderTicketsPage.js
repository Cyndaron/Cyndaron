/**
 * Copyright © 2009-2021 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more
 * details.
 */

'use strict';

const concertId = $('#concertId').val();
let tickettypes;
let deliveryType;
let addressIsAbroad = false;
let defaultDeliveryCost;
let reservedSeatCharge;

$.ajax('/api/concert/getInfo/' + concertId, {
    dataType: "json"
}).done(function (json) {
    tickettypes = json.tickettypes;
    deliveryType = parseInt(json.deliveryType);
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
    if (!checkGeneralFormFields)
    {
        return false;
    }
    if (!checkNameAndEmail())
    {
        return false;
    }

    const delivery = document.getElementById('bezorgen').checked;
    const deliveryByMemberElem = document.getElementById('deliveryByMember');
    const deliveryByMember = deliveryByMemberElem ? deliveryByMemberElem.checked : false;
    if (!checkAddressInfo(delivery, deliveryByMember))
    {
        return false;
    }

    if ((deliveryByMember || addressIsAbroad) &&
        document.getElementById('deliveryMemberName').value.length < 2)
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
    const isRequired = addressIsRequired(delivery, deliveryByMember);
    const newText = (isRequired) ? "Uw adresgegevens (verplicht):" : "Uw adresgegevens (niet verplicht):";
    document.getElementById('adresgegevensKop').innerHTML = newText;
}

function addressIsRequired(delivery, deliveryByMember)
{
    if (deliveryType === 2)
    {
        return false;
    }
    else if (deliveryByMember)
    {
        return false;
    }
    else if (deliveryType === 1)
    {
        return true;
    }
    else
    {
        return false;
    }
}

function updateVisibleTotal(total)
{
    let totalFormatted = total.toLocaleString(
        'nl-NL',
        {style: 'currency', currency: 'EUR', minimumFractionDigits: 2});
    document.getElementById('prijsvak').innerHTML = totalFormatted;
}

function updateAddressIsAbroadFields()
{
    const deliverybyMemberElem = document.getElementById('deliveryByMember');

    if (addressIsAbroad)
    {
        if (deliverybyMemberElem)
        {
            deliverybyMemberElem.checked = true;
            deliverybyMemberElem.disabled = true;
        }


        $('.postcode-related').css({display: 'none'});
    }
    else
    {
        if (deliverybyMemberElem)
        {
            deliverybyMemberElem.disabled = false;
        }

        $('.postcode-related').css({display: 'flex'});
    }
}

function zckGetDeliveryPrice(numTickets)
{
    numTickets = parseInt(numTickets);
    if (numTickets <= 0)
        return 0.0;
    if (numTickets === 1)
        return 2.0;
    if (numTickets === 2)
        return 3.0;
    if (numTickets >= 3 && numTickets <= 7)
        return 4.0;

    return 5.0;
}

function calculateTotal(delivery)
{
    const organisation = $('#organisation-value').val();
    const isZck = (organisation === 'Vlissingse Oratorium Vereniging' ||
        organisation === 'Zeeuws Concertkoor');
    let deliveryCost = 0.0;
    if (delivery)
    {
        deliveryCost = defaultDeliveryCost;
    }

    let seatSurCharge = 0.0;
    const hasReservedElement = document.getElementById('hasReservedSeats-1');
    if (hasReservedElement && hasReservedElement.checked)
    {
        seatSurCharge = reservedSeatCharge;
    }

    let total = 0.0;
    let totalNumTickets = 0;
    tickettypes.forEach(function (item) {
        let count = parseInt(document.getElementById('tickettype-' + item.id).value);
        totalNumTickets += count;
        total = total + (item.price * count);
        total = total + (seatSurCharge * count);
        if (!isZck)
            total = total + (deliveryCost * count);
    });

    if (isZck && delivery)
    {
        total += zckGetDeliveryPrice(totalNumTickets);
    }

    return total;
}

function updateForm()
{
    updateAddressIsAbroadFields();

    let delivery = false;
    let deliveryByMember = false;
    if (deliveryType === 1)
    {
        let postcode = document.getElementById('postcode').value;

        if (postcode.length < 6 && !addressIsAbroad)
        {
            document.getElementById('prijsvak').innerHTML = "€&nbsp;-";
            return;
        }

        let qualifiesForFreeDelivery = postcodeQualifiesForFreeDelivery(postcode);
        deliveryByMember = document.getElementById('deliveryByMember').checked;

        if (!qualifiesForFreeDelivery)
        {
            document.getElementById('deliveryByMember_div').style.display = "block";
        }
        else
        {
            document.getElementById('deliveryByMember_div').style.display = "none";
        }

        if (!qualifiesForFreeDelivery && !deliveryByMember)
        {
            delivery = true;
        }
        else
        {
            delivery = false;
            document.getElementById('adresgegevensKop').innerHTML =
                "Uw adresgegevens (niet verplicht):";
        }
    }
    else if (deliveryType === 0)
    {
        delivery = document.getElementById('bezorgen').checked;
    }

    let total = calculateTotal(delivery);

    updateVisibleTotal(total);
    updateAddressRequirement(delivery, deliveryByMember);
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
$('input[type=radio][name=country]').on('change', function () {
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
