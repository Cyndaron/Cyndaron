/*
 * Copyright © 2009-2016, Michael Steenbeek
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

function wissel(bewerken, veld)
{
    var oud = document.getElementById(veld + '-oud');
    var nieuw = document.getElementById(veld + '-nieuw');
    var nieuwopslaan = document.getElementById(veld + '-nieuw-opslaan');
    var nieuwannuleren = document.getElementById(veld + '-nieuw-annuleren');

    if (bewerken == true)
    {
        oud.style.display = 'none';
        nieuw.style.display = 'inline';
        nieuwopslaan.style.display = 'inline';
        nieuwannuleren.style.display = 'inline';
    }
    else
    {
        oud.style.display = 'inline';
        nieuw.style.display = 'none';
        nieuwopslaan.style.display = 'none';
        nieuwannuleren.style.display = 'none';
    }
}