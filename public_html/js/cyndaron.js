/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the ISC License. See the LICENSE file for more details.
 */

"use strict";

function email()
{
    var spantags = document.getElementsByTagName('span');
    var stlength = spantags.length;
    for (var i = stlength - 1; i >= 0; i--)
    {
        if (spantags[i].className === 'emailadres')
        {
            var address = spantags[i].childNodes[0].nodeValue + spantags[i].childNodes[2].nodeValue;
            var mailto = document.createElement('a');
            mailto.setAttribute('href', 'mailto:' + address);
            mailto.appendChild(document.createTextNode(address));
            spantags[i].parentNode.replaceChild(mailto, spantags[i]);
        }
    }
}

function geefInstelling(instelling)
{
    if (instelling === 'artikelkleur')
    {
        return $('body').first().attr('data-artikelkleur');
    }
}

$(document).ready(function ()
{
    email();
});

function del(text, yesFunc)
{
    $('#confirm-dangerous .modal-body').html(text);
    $('#confirm-dangerous-yes').off();
    $('#confirm-dangerous-yes').on('click', yesFunc);
    $('#confirm-dangerous').modal();
}

function formatEuro(amount)
{
    return amount.toLocaleString('nl-NL', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2
    });
}
