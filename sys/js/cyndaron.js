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