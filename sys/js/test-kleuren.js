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

function testkleuren()
{
    var body = document.getElementsByTagName('body');
    var bodyset = document.getElementsByName('achtergrondkleur');
    body[0].style.backgroundColor = bodyset[0].value;
    var menu = document.getElementsByClassName('menu');
    var menuset = document.getElementsByName('menukleur');
    var menuag = document.getElementsByName('menuachtergrond');
    menu[0].style.backgroundColor = menuset[0].value;
    menu[0].style.backgroundImage = "url('" + menuag[0].value + "')";
    var artikel = document.getElementsByClassName('inhoud');
    var artikelset = document.getElementsByName('artikelkleur');
    artikel[0].style.backgroundColor = artikelset[0].value;
}

$('#testKleuren').on('click', testkleuren());